<?php

namespace QuickBooksOnline\Payments\Interceptors;

use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface};
use QuickBooksOnline\Payments\HttpClients\Response\{ResponseInterface};

interface InterceptorInterface
{
    /**
     * Change the $request before the request is going to be sent.
     * @param RequestInterface $request The request to be sent.
     * @param PaymentClient $client The payment client that handles the request and response.
     */
    public function before(RequestInterface &$request, PaymentClient $client) : void;

    /**
     * Change the $response after the response has been received.
     * @param ResponseInterface $response The response received.
     * @param PaymentClient $client The payment client that handles the request and response.
     */
    public function after(ResponseInterface &$response, PaymentClient $client) : void;

    /**
     * Intercepting the request sent to QuickBooks Online or response received from QuickBooks Online,
     * or both. It does not modify the request or response. In order to alter the request or response, use the
     * before() and after() methods.
     * @param RequestInterface $request The request to be intercept.
     * @param ResponseInterface $response The response to be intercept.
     * @param PaymentClient $client The payment client that handles the request and response.
     */
    public function intercept(RequestInterface $request, ResponseInterface $response, PaymentClient $client) : void;
}
