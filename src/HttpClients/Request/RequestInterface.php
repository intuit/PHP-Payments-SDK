<?php
namespace QuickBooksOnline\Payments\HttpClients\Request;

/**
 *
 */
interface RequestInterface
{
    const GET = "GET";
    const POST = "POST";
    const DELETE = "DELETE";
    public function setMethod($method);
    public function getMethod();
    public function setUrl($url);
    public function getUrl();
    public function setHeader($header);
    public function getHeader();
    public function setBody($body);
    public function getBody();
    public function getRequestType();
    public function setRequestType($type);
    public function setRequestId($requestId);
    public function getRequestId();
}
