<?php declare(strict_types = 1);

namespace BrandEmbassy\MockeryTools\Http\Exchange;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestOnlyExpectedExchange
{
    private RequestInterface $request;

    private ResponseInterface $response;


    public function __construct(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $this->request = $request;
        $this->response = $response;
    }


    public function getRequest(): RequestInterface
    {
        return $this->request;
    }


    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
