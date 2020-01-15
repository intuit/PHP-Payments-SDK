<?php
namespace QuickBooksOnline\Payments\HttpClients\core;

use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Response\IntuitResponse;
use QuickBooksOnline\Payments\HttpClients\Response\ResponseInterface;
use QuickBooksOnline\Payments\HttpClients\Response\ResponseFactory;

class HttpCurlClient implements HttpClientInterface
{
    private $baseCurl;
    //Default 10 seconds
    private $connectionTimeOut;
    //Default 100 seconds
    private $requestTimeOut;
    //Default true;
    private $isVerifySSL;

    private $enableDebug;
    private $information;

    /**
     * The last request sent by the client. Regardless success or failure.
     */
    private $lastRequest;

    public function __construct()
    {
        $this->baseCurl = new BaseCurl();
        $connectionTimeOut = 10;
        $requestTimeOut = 100;
        $isVerifySSL = true;
    }

    public function setTimeOut(int $userSetConnectionTimeout, int $userSetRequestTimeout) : void
    {
        $this->connectionTimeOut = $userSetConnectionTimeout;
        $this->requestTimeOut = $userSetRequestTimeout;
    }

    public function setVerifySSL(bool $isBuiltInSSLVerifierUsed) : void
    {
        $this->isVerifySSL = $isBuiltInSSLVerifierUsed;
    }

    public function send(RequestInterface $request) : ResponseInterface
    {
        if (!isset($request)) {
            throw new \RuntimeException("Cannot send an empty request.");
        }
        $this->lastRequest = $request;
        $this->prepare($request);
        $curlResponse = $this->execute();
        $this->handleCurlErrors();
        $response = $this->parseCurlResponse($curlResponse, $request);
        if ($this->enableDebug) {
            $this->information = curl_getinfo($this->baseCurl->getCurl());
        }
        $this->closeConnection();
        return $response;
    }

    private function execute()
    {
        return $this->baseCurl->execute();
    }

    private function handleCurlErrors()
    {
        if ($this->baseCurl->errno() || $this->baseCurl->error()) {
            $errorMsg = $this->baseCurl->error();
            $errorNumber = $this->baseCurl->errno();
            throw new \RuntimeException("cURL error during making API call. cURL Error Number:[" . $errorNumber . "] with error:[" . $errorMsg . "]");
        }
    }

    private function parseCurlResponse($curlResponse, $request) : ResponseInterface
    {
        $headerSize = $this->baseCurl->getInfo(CURLINFO_HEADER_SIZE);
        $rawHeaders = mb_substr($curlResponse, 0, $headerSize);
        $rawBody = mb_substr($curlResponse, $headerSize);
        $httpStatusCode = $this->baseCurl->getInfo(CURLINFO_HTTP_CODE);

        return ResponseFactory::createStandardIntuitResponse($httpStatusCode, $rawHeaders, $rawBody, $request);
    }

    /**
     * close the connection of current http client
     */
    private function closeConnection()
    {
        $this->baseCurl->close();
    }


    public function getLastSentRequest() : RequestInterface
    {
        return $this->lastRequest;
    }

    /**
     * The body would need to be in the correct format. It would need to match the header.
     *
     */
    private function prepare(RequestInterface $request) : void
    {
        $this->intializeCurl();
        if ($this->enableDebug) {
            $this->enableheaderOut();
        }
        $this->baseCurl->setHeader($request->getHeader());
        $this->baseCurl->setUrl($request->getUrl());
        if (strcmp($request->getMethod(), RequestInterface::POST) === 0) {
            $this->setPostBodyAndMethod($request);
        } else {
            $this->baseCurl->setupOption(CURLOPT_CUSTOMREQUEST, $request->getMethod());
        }
        $this->baseCurl->setupOption(CURLOPT_SSL_VERIFYPEER, true);
        if ($this->isVerifySSL) {
            $this->setSSLConfig();
        } else {
            $this->acceptAll();
        }
        $this->updateCurlSettings();
    }

    private function enableheaderOut()
    {
        $this->baseCurl->setupOption(CURLINFO_HEADER_OUT, true);
    }

    private function intializeCurl()
    {
        if ($this->baseCurl->isCurlSet()) {
            return;
        } else {
            $this->baseCurl->init();
        }
    }

    private function setPostBodyAndMethod(RequestInterface $request)
    {
        $this->baseCurl->setupOption(CURLOPT_POST, true);
        $this->baseCurl->setBody($request->getBody());
    }

    private function setSSLConfig()
    {
        $this->baseCurl->setupOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->baseCurl->setupOption(CURLOPT_CAINFO, CoreConstants::getCertPath());
    }

    private function acceptAll()
    {
        $this->baseCurl->setupOption(CURLOPT_SSL_VERIFYHOST, 0);
    }

    private function updateCurlSettings()
    {
        $this->baseCurl->setupOption(CURLOPT_CONNECTTIMEOUT, $this->connectionTimeOut);
        $this->baseCurl->setupOption(CURLOPT_TIMEOUT, $this->requestTimeOut);
        $this->baseCurl->setupOption(CURLOPT_RETURNTRANSFER, true);
        $this->baseCurl->setupOption(CURLOPT_HEADER, true);
    }

    public function enableDebug()
    {
        $this->enableDebug = true;
    }

    public function getDebugInfo()
    {
        return $this->information;
    }
}
