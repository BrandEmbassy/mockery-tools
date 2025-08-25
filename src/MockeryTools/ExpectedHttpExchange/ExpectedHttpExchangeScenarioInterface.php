<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\ExpectedHttpExchange;

use Psr\Http\Message\RequestInterface;

interface ExpectedHttpExchangeScenarioInterface
{
    public function getRequest(): RequestInterface;
}
