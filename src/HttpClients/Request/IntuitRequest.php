<?php
namespace QuickBooksOnline\Payments\HttpClients\Request;

class IntuitRequest implements RequestInterface
{
    private $method;
    private $url;
    private $header;
    private $body;
    private $requestType;

    public function __construct($type)
    {
        $this->setRequestType($type);
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        if (!isset($url) || empty($url)) {
            throw new InvalidArgumentException("invalid URL.");
        } else {
            $this->url = $url;
        }
        return $this;
    }


    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        if (isset($header) && !empty($header) && is_array($header)) {
            $this->header = $header;
        } else {
            throw new InvalidArgumentException("invalid header for request");
        }

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        if ($this->getMethod() !== RequestInterface::POST) {
            throw new InvalidArgumentException("Cannot Set body for GET request");
        }
        $this->body = $body;
        return $this;
    }

    public function getRequestType()
    {
        return $this->requestType;
    }
    public function setRequestType($type)
    {
        $this->requestType = $type;
    }
}
