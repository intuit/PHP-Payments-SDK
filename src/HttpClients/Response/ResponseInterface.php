<?php
namespace QuickBooksOnline\Payments\HttpClients\Response;

use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;

/**
 *
 */
interface ResponseInterface
{
    /**
     *  Set if the request is made successful. It will also update the request to be failed
     *  if the status code is non 200.
     */
    public function setResponseStatus(int $code);
    public function setHeader($responseHeader);
    public function setBody($responseBody);
    public function setAssociatedRequest(RequestInterface $associatedRequest);
    public function getStatusCode() : int;
    public function getUrl() : string;
    public function getHeader() : array;
    public function getBody();
    public function failed() : bool;
    public function getIntuitTid() : string;
    public function getContentType() : string;
    public function getAssociatedRequest() : RequestInterface;
    public function getRequestId(): string;
}
