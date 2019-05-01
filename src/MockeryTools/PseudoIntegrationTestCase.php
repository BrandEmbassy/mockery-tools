<?php declare(strict_types = 1);

namespace BE\MockeryTools;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\RequestOptions;
use Mockery;
use Mockery\MockInterface;
use Nette\DI\Container;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PHPUnit\Framework\TestCase;
use function md5;

abstract class PseudoIntegrationTestCase extends TestCase
{
    /**
     * @var mixed[]
     */
    private $replacedServices;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Client|MockInterface
     */
    protected $httpClientMock;


    public function setUp(): void
    {
        $this->container = $this->createContainer();
        $this->replacedServices = $this->loadMockServices();
        $this->httpClientMock = $this->container->getByType(Client::class);

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
        $container = ContainerFactory::create(
            $this->getConfigFiles(),
            $this->getTempDirectory(),
            'pseudo-integration-' . md5(__CLASS__)
        );

        return $container;
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
    private function replaceService(string $serviceName, $service): void
    {
        $this->container->removeService($serviceName);
        $this->container->addService($serviceName, $service);
    }


    /**
     * @return mixed[]
     */
    protected function loadArrayFromJsonFile(string $filePath): array
    {
        $fileContent = FileSystem::read($filePath);

        return Json::decode($fileContent, Json::FORCE_ARRAY);
    }


    /**
     * @param mixed[]      $responseBody
     * @param mixed[]|null $requestOptions
     */
    protected function expectRequest(
        string $method,
        string $url,
        array $responseBody = [],
        ?array $requestOptions = null
    ): void {
        $psrResponse = new PsrResponse(200, [], Json::encode($responseBody));

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
        array $responseBody = [],
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
        array $responseBody = [],
        array $requestOptions = []
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectAuthorizedRequest($method, $url, $bearerToken, $responseBody, $requestOptions);
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
        array $responseBody = [],
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
        array $responseBody = [],
        ?array $requestOptions = null
    ): void {
        $psrResponse = new PsrResponse($errorCode, [], Json::encode($responseBody));

        $clientException = new ClientException('Client error', new PsrRequest($method, $url), $psrResponse);

        $this->httpClientMock->shouldReceive('request')
            ->with($method, $url, $requestOptions ?? Mockery::any())
            ->once()
            ->andThrow($clientException);
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


    abstract protected function getPlatformApiHost();
}
