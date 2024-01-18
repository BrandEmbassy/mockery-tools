<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\PseudoIntegration;

use BrandEmbassy\MockeryTools\RequestOptionsMatcher\RequestOptionsMatcher;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Expectation;
use Mockery\Matcher\MatcherInterface;
use Mockery\MockInterface;
use Nette\DI\Container;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use function get_class;
use function implode;
use function md5;
use function sprintf;

abstract class PseudoIntegrationTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var mixed[]
     */
    protected array $replacedServices;

    protected Container $container;

    /**
     * @var Client&MockInterface
     */
    protected $httpClientMock;


    protected function setUp(): void
    {
        $this->container = $this->createContainer();
        $this->replacedServices = $this->loadMockServices();

        /** @var Client&MockInterface $httpClient */
        $httpClient = $this->container->getByType(Client::class);
        $this->httpClientMock = $httpClient;

        parent::setUp();
    }


    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->replacedServices as $serviceName => $service) {
            $this->replaceService($serviceName, $service);
        }

        $this->replacedServices = [];
    }


    private function createContainer(): Container
    {
        return ContainerFactory::create(
            $this->getConfigFiles(),
            $this->getTempDirectory(),
            'pseudo-integration-' . md5(implode('-', $this->getConfigFiles())),
        );
    }


    /**
     * @return mixed
     */
    protected function getService(string $serviceName)
    {
        return $this->container->getService($serviceName);
    }


    /**
     * @return mixed[]
     */
    private function loadMockServices(): array
    {
        $replacedServiceMocks = [];

        foreach ($this->getServiceMocks() as $serviceName => $servicesMock) {
            $replacedServiceMocks[$serviceName] = $this->container->getService($serviceName);
            $this->replaceService($serviceName, $servicesMock);
        }

        return $replacedServiceMocks;
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     *
     * @param mixed $service
     */
    protected function replaceService(string $serviceName, $service): void
    {
        $this->container->removeService($serviceName);
        $this->container->addService($serviceName, $service);
    }


    /**
     * @param array<string, string> $headersToAdd
     */
    private function addHeadersToMatcher(MatcherInterface $requestOptionsMatcher, array $headersToAdd): MatcherInterface
    {
        if ($requestOptionsMatcher instanceof RequestOptionsMatcher) {
            $matcher = $requestOptionsMatcher;
            foreach ($headersToAdd as $headerName => $headerValue) {
                $matcher = $requestOptionsMatcher->withHeader($headerName, $headerValue);
            }

            return $matcher;
        }

        throw new LogicException(
            sprintf(
                'Cannot add header to matcher of type %s. Use method which does not manipulate with headers or use %s instead',
                get_class($requestOptionsMatcher),
                RequestOptionsMatcher::class,
            ),
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    protected function expectRequest(
        string $method,
        string $url,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        return $this->expectRequestWithStringResponse($method, $url, $requestOptionsMatcher, $encodedResponseBody);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @return Expectation
     */
    protected function expectRequestWithStringResponse(
        string $method,
        string $url,
        MatcherInterface $requestOptionsMatcher,
        ?string $responseBody = ''
    ) {
        $psrResponse = new PsrResponse(200, [], $responseBody);

        return $this->httpClientMock->shouldReceive('request')
            ->with($method, $url, $requestOptionsMatcher)
            ->once()
            ->andReturn($psrResponse);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    protected function expectAuthorizedRequest(
        string $method,
        string $url,
        string $bearerToken,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $matcher = $this->addHeadersToMatcher($requestOptionsMatcher, ['Authorization' => 'Bearer ' . $bearerToken]);

        return $this->expectRequest(
            $method,
            $url,
            $matcher,
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    public function expectPlatformAuthorizedRequest(
        string $method,
        string $platformEndpoint,
        string $bearerToken,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        return $this->expectAuthorizedRequest(
            $method,
            $url,
            $bearerToken,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    public function expectPlatformAuthorizedRequestFail(
        string $method,
        string $platformEndpoint,
        string $bearerToken,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        return $this->expectAuthorizedRequestFail(
            $method,
            $url,
            $bearerToken,
            $errorCode,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    public function expectDfo3PlatformAuthorizedRequest(
        string $method,
        string $platformEndpoint,
        string $bearerToken,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $url = $this->getPlatformApiHostDfo3() . $platformEndpoint;

        return $this->expectAuthorizedRequest(
            $method,
            $url,
            $bearerToken,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    public function expectDfo3PlatformAuthorizedRequestFail(
        string $method,
        string $platformEndpoint,
        string $bearerToken,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $url = $this->getPlatformApiHostDfo3() . $platformEndpoint;

        return $this->expectAuthorizedRequestFail(
            $method,
            $url,
            $bearerToken,
            $errorCode,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    public function expectGoldenPlatformRequest(
        string $method,
        string $platformEndpoint,
        string $goldenKey,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        return $this->expectRequest(
            $method,
            $url,
            $this->addHeadersToMatcher($requestOptionsMatcher, ['X-Api-Token' => $goldenKey]),
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    public function expectGoldenPlatformRequestFail(
        string $method,
        string $platformEndpoint,
        string $goldenKey,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        return $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $this->addHeadersToMatcher($requestOptionsMatcher, ['X-Api-Token' => $goldenKey]),
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @return Expectation
     */
    protected function expectAuthorizedRequestWithStringResponseFail(
        string $method,
        string $url,
        string $bearerToken,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?string $responseBody = ''
    ) {
        return $this->expectRequestWithStringResponseFail(
            $method,
            $url,
            $errorCode,
            $this->addHeadersToMatcher($requestOptionsMatcher, ['Authorization' => 'Bearer ' . $bearerToken]),
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    protected function expectAuthorizedRequestFail(
        string $method,
        string $url,
        string $bearerToken,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        return $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $this->addHeadersToMatcher($requestOptionsMatcher, ['Authorization' => 'Bearer ' . $bearerToken]),
            $responseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $responseBody
     *
     * @return Expectation
     */
    protected function expectRequestFail(
        string $method,
        string $url,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ) {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        return $this->expectRequestWithStringResponseFail(
            $method,
            $url,
            $errorCode,
            $requestOptionsMatcher,
            $encodedResponseBody,
        );
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @return Expectation
     */
    protected function expectRequestWithStringResponseFail(
        string $method,
        string $url,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?string $responseBody = ''
    ) {
        $psrResponse = new PsrResponse($errorCode, [], $responseBody);

        $guzzleException = RequestException::create(new PsrRequest($method, $url), $psrResponse);

        return $this->httpClientMock->shouldReceive('request')
            ->with($method, $url, $requestOptionsMatcher)
            ->once()
            ->andThrow($guzzleException);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $requestOptions
     *
     * @return Expectation
     */
    protected function expectFileContentRequest(
        string $fileUrl,
        string $fileContent,
        string $contentType = '',
        ?array $requestOptions = null
    ) {
        $psrResponse = new PsrResponse(200, ['Content-Type' => $contentType], $fileContent);

        return $this->httpClientMock->expects('request')
            ->with('GET', $fileUrl, $requestOptions ?? Mockery::any())
            ->andReturn($psrResponse);
    }


    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     *
     * @param mixed[] $requestOptions
     *
     * @return Expectation
     */
    protected function expectFileContentRequestFail(
        string $fileUrl,
        int $errorCode = 400,
        ?string $responseBody = '',
        ?array $requestOptions = null
    ) {
        $psrResponse = new PsrResponse($errorCode, [], $responseBody);

        $guzzleException = RequestException::create(new PsrRequest('GET', $fileUrl), $psrResponse);

        return $this->httpClientMock->expects('request')
            ->with('GET', $fileUrl, $requestOptions ?? Mockery::any())
            ->andThrow($guzzleException);
    }


    /**
     * @return mixed[]
     */
    protected function getServiceMocks(): array
    {
        return [];
    }


    /**
     * @return string[]
     */
    abstract protected function getConfigFiles(): array;


    abstract protected function getTempDirectory(): string;


    abstract protected function getPlatformApiHost(): string;


    abstract protected function getPlatformApiHostDfo3(): string;
}
