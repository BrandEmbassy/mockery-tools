<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\PseudoIntegration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\ExpectationInterface;
use Mockery\Matcher\MatcherInterface;
use Mockery\MockInterface;
use Nette\DI\Container;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use function implode;
use function md5;

abstract class PseudoIntegrationTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var mixed[]
     */
    protected array $replacedServices;

    protected Container $container;

    protected MockInterface&Client $httpClientMock;


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


    protected function getService(string $serviceName): mixed
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


    protected function replaceService(string $serviceName, mixed $service): void
    {
        $this->container->removeService($serviceName);
        $this->container->addService($serviceName, $service);
    }


    /**
     * @param mixed[] $responseBody
     */
    protected function expectRequest(
        string $method,
        string $url,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): ExpectationInterface {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        return $this->expectRequestWithStringResponse($method, $url, $requestOptionsMatcher, $encodedResponseBody);
    }


    protected function expectRequestWithStringResponse(
        string $method,
        string $url,
        MatcherInterface $requestOptionsMatcher,
        ?string $responseBody = ''
    ): ExpectationInterface {
        $psrResponse = new PsrResponse(200, [], $responseBody);

        return $this->httpClientMock->expects('request')
            ->with($method, $url, $requestOptionsMatcher)
            ->andReturn($psrResponse);
    }


    /**
     * @param mixed[] $responseBody
     */
    public function expectPlatformRequest(
        string $method,
        string $platformEndpoint,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): ExpectationInterface {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        return $this->expectRequest(
            $method,
            $url,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[] $responseBody
     */
    public function expectPlatformRequestFail(
        string $method,
        string $platformEndpoint,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): ExpectationInterface {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        return $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[] $responseBody
     */
    public function expectDfo3PlatformRequest(
        string $method,
        string $platformEndpoint,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): ExpectationInterface {
        $url = $this->getPlatformApiHostDfo3() . $platformEndpoint;

        return $this->expectRequest(
            $method,
            $url,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[] $responseBody
     */
    public function expectDfo3PlatformRequestFail(
        string $method,
        string $platformEndpoint,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): ExpectationInterface {
        $url = $this->getPlatformApiHostDfo3() . $platformEndpoint;

        return $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[] $responseBody
     */
    protected function expectRequestFail(
        string $method,
        string $url,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): ExpectationInterface {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        return $this->expectRequestWithStringResponseFail(
            $method,
            $url,
            $errorCode,
            $requestOptionsMatcher,
            $encodedResponseBody,
        );
    }


    protected function expectRequestWithStringResponseFail(
        string $method,
        string $url,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?string $responseBody = ''
    ): ExpectationInterface {
        $psrResponse = new PsrResponse($errorCode, [], $responseBody);

        $guzzleException = RequestException::create(new PsrRequest($method, $url), $psrResponse);

        return $this->httpClientMock->expects('request')
            ->with($method, $url, $requestOptionsMatcher)
            ->andThrow($guzzleException);
    }


    /**
     * @param mixed[] $requestOptions
     */
    protected function expectFileContentRequest(
        string $fileUrl,
        string $fileContent,
        string $contentType = '',
        ?array $requestOptions = null
    ): ExpectationInterface {
        $psrResponse = new PsrResponse(200, ['Content-Type' => $contentType], $fileContent);

        return $this->httpClientMock->expects('request')
            ->with('GET', $fileUrl, $requestOptions ?? Mockery::any())
            ->andReturn($psrResponse);
    }


    /**
     * @param mixed[] $requestOptions
     */
    protected function expectFileContentRequestFail(
        string $fileUrl,
        int $errorCode,
        ?string $responseBody = '',
        ?array $requestOptions = null
    ): ExpectationInterface {
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
