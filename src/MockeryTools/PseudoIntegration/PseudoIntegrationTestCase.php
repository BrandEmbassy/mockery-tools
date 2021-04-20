<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\PseudoIntegration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\RequestOptions;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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
    protected $replacedServices;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Client&MockInterface
     */
    protected $httpClientMock;


    public function setUp(): void
    {
        $this->container = $this->createContainer();

        /** @var Client&MockInterface $httpClient */
        $httpClient = $this->container->getByType(Client::class);
        $this->httpClientMock = $httpClient;

        $this->replacedServices = $this->loadMockServices();

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
            'pseudo-integration-' . md5(implode('-', $this->getConfigFiles()))
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
     * @param mixed[] $responseBody
     * @param mixed[]|null $requestOptions
     */
    protected function expectRequest(
        string $method,
        string $url,
        ?array $responseBody = null,
        ?array $requestOptions = null
    ): void {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        $this->expectRequestWithStringResponse($method, $url, $encodedResponseBody, $requestOptions);
    }


    /**
     * @param mixed[]|null $requestOptions
     */
    protected function expectRequestWithStringResponse(
        string $method,
        string $url,
        ?string $responseBody = '',
        ?array $requestOptions = []
    ): void {
        $psrResponse = new PsrResponse(200, [], $responseBody);

        $this->httpClientMock->shouldReceive('request')
            ->with($method, $url, $requestOptions ?? Mockery::any())
            ->once()
            ->andReturn($psrResponse);
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    protected function expectAuthorizedRequest(
        string $method,
        string $url,
        string $bearerToken,
        ?array $responseBody = null,
        array $requestOptions = []
    ): void {
        $this->expectRequest(
            $method,
            $url,
            $responseBody,
            $requestOptions + [RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $bearerToken]]
        );
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    public function expectPlatformAuthorizedRequest(
        string $method,
        string $platformEndpoint,
        string $bearerToken,
        ?array $responseBody = null,
        array $requestOptions = []
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectAuthorizedRequest(
            $method,
            $url,
            $bearerToken,
            $responseBody,
            $requestOptions
        );
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    public function expectPlatformAuthorizedRequestFail(
        string $method,
        string $platformEndpoint,
        string $bearerToken,
        int $errorCode,
        ?array $responseBody = null,
        array $requestOptions = []
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectAuthorizedRequestFail($method, $url, $bearerToken, $errorCode, $responseBody, $requestOptions);
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    public function expectGoldenPlatformRequest(
        string $method,
        string $platformEndpoint,
        string $goldenKey,
        ?array $responseBody = null,
        array $requestOptions = []
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectRequest(
            $method,
            $url,
            $responseBody,
            $requestOptions + [RequestOptions::HEADERS => ['X-Api-Token' => $goldenKey]]
        );
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    public function expectGoldenPlatformRequestFail(
        string $method,
        string $platformEndpoint,
        string $goldenKey,
        int $errorCode = 400,
        ?array $responseBody = null,
        array $requestOptions = []
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $responseBody,
            $requestOptions + [RequestOptions::HEADERS => ['X-Api-Token' => $goldenKey]]
        );
    }


    /**
     * @param mixed[] $requestOptions
     */
    protected function expectAuthorizedRequestWithStringResponseFail(
        string $method,
        string $url,
        string $bearerToken,
        int $errorCode = 400,
        ?string $responseBody = '',
        array $requestOptions = []
    ): void {
        $this->expectRequestWithStringResponseFail(
            $method,
            $url,
            $errorCode,
            $responseBody,
            $requestOptions + [RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $bearerToken]]
        );
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    protected function expectAuthorizedRequestFail(
        string $method,
        string $url,
        string $bearerToken,
        int $errorCode = 400,
        ?array $responseBody = null,
        array $requestOptions = []
    ): void {
        $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $responseBody,
            $requestOptions + [RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $bearerToken]]
        );
    }


    /**
     * @param mixed[] $responseBody
     * @param mixed[] $requestOptions
     */
    protected function expectRequestFail(
        string $method,
        string $url,
        int $errorCode = 400,
        ?array $responseBody = null,
        ?array $requestOptions = []
    ): void {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        $this->expectRequestWithStringResponseFail(
            $method,
            $url,
            $errorCode,
            $encodedResponseBody,
            $requestOptions
        );
    }


    /**
     * @param mixed[] $requestOptions
     */
    protected function expectRequestWithStringResponseFail(
        string $method,
        string $url,
        int $errorCode = 400,
        ?string $responseBody = '',
        ?array $requestOptions = []
    ): void {
        $psrResponse = new PsrResponse($errorCode, [], $responseBody);

        $guzzleException = RequestException::create(new PsrRequest($method, $url), $psrResponse);

        $this->httpClientMock->shouldReceive('request')
            ->with($method, $url, $requestOptions ?? Mockery::any())
            ->once()
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
}
