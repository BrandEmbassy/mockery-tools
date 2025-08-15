<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\PseudoIntegration;

use BrandEmbassy\MockeryTools\ExpectedHttpExchange\ExpectedHttpExchangeFactory;
use BrandEmbassy\MockeryTools\ExpectedHttpExchange\ExpectedHttpExchangeHandler;
use BrandEmbassy\MockeryTools\RequestOptionsMatcher\RequestOptionsMatcher;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\Matcher\MatcherInterface;
use Nette\DI\Container;
use Nette\Utils\Json;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use function assert;
use function implode;
use function md5;
use const PHP_EOL;

abstract class PseudoIntegrationTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var mixed[]
     */
    protected array $replacedServices;

    protected Container $container;

    protected ExpectedHttpExchangeHandler $expectedHttpExchangeHandler;

    protected ExpectedHttpExchangeFactory $expectedHttpExchangeFactory;


    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createContainer();
        $this->replacedServices = $this->loadMockServices();

        $this->expectedHttpExchangeHandler = $this->getExpectedHttpExchangeHandler();
        $this->expectedHttpExchangeFactory = $this->getExpectedHttpExchangeFactory();
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


    protected function replaceService(string $serviceName, object $service): void
    {
        $this->container->removeService($serviceName);
        $this->container->addService($serviceName, $service);
    }


    #[PostCondition]
    protected function assertNoExtraExpectedHttpExchangesArePresent(): void
    {
        if (!$this->expectedHttpExchangeHandler->hasExpectedExchanges()) {
            return;
        }

        $formattedRequests = '';
        foreach ($this->expectedHttpExchangeHandler->getExpectedExchanges() as $expectedExchange) {
            $expectedRequest = $expectedExchange->getRequest();
            $formattedRequests = '[' . $expectedRequest->getMethod() . '] ' . $expectedRequest->getUri() . PHP_EOL;
        }

        Assert::fail('There are some expected requests that were not called:' . PHP_EOL . $formattedRequests);
    }


    /**
     * @param mixed[] $responseHeaders
     * @param mixed[]|null $responseBody
     */
    protected function expectRequest(
        string $method,
        string $url,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null,
        array $responseHeaders = [],
        int $responseStatusCode = 200,
    ): void {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        $this->expectRequestWithStringResponse(
            $method,
            $url,
            $requestOptionsMatcher,
            $encodedResponseBody,
            $responseHeaders,
            $responseStatusCode,
        );
    }


    /**
     * @param mixed[] $responseHeaders
     */
    protected function expectRequestWithStringResponse(
        string $method,
        string $url,
        MatcherInterface $requestOptionsMatcher,
        ?string $responseBody = '',
        array $responseHeaders = [],
        int $responseStatusCode = 200,
    ): void {
        assert($requestOptionsMatcher instanceof RequestOptionsMatcher);

        $this->expectedHttpExchangeHandler->expectExchange(
            $this->expectedHttpExchangeFactory->createExchange(
                $method,
                $url,
                $responseStatusCode,
                $responseBody ?? '',
                '',
                $requestOptionsMatcher->getSortedExpectedRequestOptions(),
                $responseHeaders,
            ),
        );
    }


    /**
     * @param mixed[]|null $responseBody
     */
    public function expectPlatformRequest(
        string $method,
        string $platformEndpoint,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectRequest(
            $method,
            $url,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[]|null $responseBody
     */
    public function expectPlatformRequestFail(
        string $method,
        string $platformEndpoint,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): void {
        $url = $this->getPlatformApiHost() . $platformEndpoint;

        $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[]|null $responseBody
     */
    public function expectDfo3PlatformRequest(
        string $method,
        string $platformEndpoint,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): void {
        $url = $this->getPlatformApiHostDfo3() . $platformEndpoint;

        $this->expectRequest(
            $method,
            $url,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[]|null $responseBody
     */
    public function expectDfo3PlatformRequestFail(
        string $method,
        string $platformEndpoint,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): void {
        $url = $this->getPlatformApiHostDfo3() . $platformEndpoint;

        $this->expectRequestFail(
            $method,
            $url,
            $errorCode,
            $requestOptionsMatcher,
            $responseBody,
        );
    }


    /**
     * @param mixed[]|null $responseBody
     */
    protected function expectRequestFail(
        string $method,
        string $url,
        int $errorCode,
        MatcherInterface $requestOptionsMatcher,
        ?array $responseBody = null
    ): void {
        $encodedResponseBody = $responseBody === null ? null : Json::encode($responseBody);

        $this->expectRequestWithStringResponseFail(
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
    ): void {
        assert($requestOptionsMatcher instanceof RequestOptionsMatcher);

        $this->expectedHttpExchangeHandler->expectExchange(
            $this->expectedHttpExchangeFactory->createExchange(
                $method,
                $url,
                $errorCode,
                $responseBody ?? '',
                '',
                $requestOptionsMatcher->getSortedExpectedRequestOptions(),
            ),
        );
    }


    /**
     * @param mixed[]|null $requestOptions
     */
    protected function expectFileContentRequest(
        string $fileUrl,
        string $fileContent,
        string $contentType = '',
        ?array $requestOptions = null
    ): void {
        $this->expectedHttpExchangeHandler->expectExchange(
            $this->expectedHttpExchangeFactory->createExchange(
                'GET',
                $fileUrl,
                200,
                $fileContent,
                $contentType,
                $requestOptions ?? [],
            ),
        );
    }


    /**
     * @param mixed[]|null $requestOptions
     */
    protected function expectFileContentRequestFail(
        string $fileUrl,
        int $errorCode,
        ?string $responseBody = '',
        ?array $requestOptions = null
    ): void {
        $this->expectedHttpExchangeHandler->expectExchange(
            $this->expectedHttpExchangeFactory->createExchange(
                'GET',
                $fileUrl,
                $errorCode,
                $responseBody ?? '',
                '',
                $requestOptions ?? [],
            ),
        );
    }


    protected function expectRequestNetworkFail(
        string $method,
        string $url,
        ClientExceptionInterface $exception,
        RequestOptionsMatcher $requestOptionsMatcher,
    ): void {
        $this->expectedHttpExchangeHandler->expectExchange(
            $this->expectedHttpExchangeFactory->createFailedRequest(
                $method,
                $url,
                $exception,
                $requestOptionsMatcher->getSortedExpectedRequestOptions(),
            ),
        );
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


    abstract protected function getExpectedHttpExchangeHandler(): ExpectedHttpExchangeHandler;


    abstract protected function getExpectedHttpExchangeFactory(): ExpectedHttpExchangeFactory;
}
