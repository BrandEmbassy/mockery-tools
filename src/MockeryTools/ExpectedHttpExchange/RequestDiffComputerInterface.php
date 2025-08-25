<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\ExpectedHttpExchange;

use Psr\Http\Message\RequestInterface;

interface RequestDiffComputerInterface
{
    /**
     * @param ExpectedHttpExchangeScenarioInterface[] $expectedHttpExchanges
     */
    public function outputRequestDiffs(RequestInterface $actualRequest, array $expectedHttpExchanges): void;
}
