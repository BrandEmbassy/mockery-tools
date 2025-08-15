<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\ExpectedHttpExchange;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

readonly class ExpectedFailedHttpExchange implements ExpectedHttpExchangeScenarioInterface
{
    public function __construct(
        private RequestInterface $request,
        private ClientExceptionInterface $exception
    ) {
    }


    public function getRequest(): RequestInterface
    {
        return $this->request;
    }


    public function getException(): ClientExceptionInterface
    {
        return $this->exception;
    }
}
