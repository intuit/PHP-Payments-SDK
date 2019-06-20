<?php
namespace QuickBooksOnline\Payments\HttpClients\Response;

use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;

final class ResponseFactory
{
    public static function createStandardIntuitResponse($httpStatusCode, $rawHeaders, $rawBody, RequestInterface $request)
    {
        $response = new IntuitResponse();
        $response->setResponseStatus($httpStatusCode);
        $response->setHeader($rawHeaders);
        $response->setBody($rawBody);
        $response->setAssociatedRequest($request);
        return $response;
    }
}
