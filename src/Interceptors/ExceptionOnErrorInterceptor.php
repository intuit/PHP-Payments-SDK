<?php

namespace QuickBooksOnline\Payments\Interceptors;

use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface};
use QuickBooksOnline\Payments\HttpClients\Response\{ResponseInterface};
use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Operations\ChargeOperations;

class ExceptionOnErrorInterceptor implements InterceptorInterface
{
    public function before(RequestInterface &$request, PaymentClient $client) : void
    {
    }

    public function after(ResponseInterface &$response, PaymentClient $client): void
    {
        if ($response->failed()) {
            $request = $response->getAssociatedRequest();
            throw new \RuntimeException("Request: [" . $request->getMethod() . " " . $request->getRequestType() . "]" . $request->getUrl() . " failed:" .
            $response->getBody() . " intuit-tid: " . $response->getIntuitTid());
        }
    }

    public function intercept(RequestInterface $request, ResponseInterface $response, PaymentClient $client) : void
    {
    }
}
