<?php
namespace QuickBooksOnline\Payments\HttpClients\Response;

use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\core\CoreConstants;

class IntuitResponse implements ResponseInterface
{
    private $code;
    private $url;
    private $header;
    private $body;
    private $failure;
    private $intuitTid;
    private $contentType;
    private $request;
    private $requestId;

    public function setResponseStatus(int $statusCode)
    {
        $this->code = $statusCode;
        $this->checkIfRequestIsFailed($statusCode);
        return $this;
    }

    private function checkIfRequestIsFailed(int $statusCode)
    {
        if ($statusCode < 200 || $statusCode >= 300) {
            $this->failure = true;
        } else {
            $this->failure = false;
        }
    }

    public function getStatusCode() : int
    {
        return $this->code;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function setHeader($responseHeader)
    {
        if (is_string($responseHeader)) {
            $this->header = $this->convertStringHeaderToArray($responseHeader);
        } else {
            $this->header = $responseHeader;
            $this->intuitTid = $responseHeader['intuit_tid'];
            $this->contentType = $responseHeader['Content-Type'];
        }

        return $this;
    }

    /**
    * Parse the raw Http Response Header to associated array to be consumered.
    * It will also store the Content-Type of the response.
    * @param string rawHeaders
    */
    private function convertStringHeaderToArray($rawHeaders)
    {
        $headers = array();
        $rawHeaders = str_replace("\r\n", "\n", $rawHeaders);
        $response_headers_rows = explode("\n", trim($rawHeaders));
        foreach ($response_headers_rows as $line) {
            if (strpos($line, ': ') == false) {
                continue;
            } else {
                list($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
                //set response content type
                $this->setContentType($key, $value);
                $this->setIntuitTid($key, $value);
            }
        }

        return $headers;
    }

    public function getHeader() : array
    {
        return $this->header;
    }
    public function setBody($responseBody)
    {
        $this->body = $responseBody;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function failed() : bool
    {
        return $this->failure;
    }

    private function setIntuitTid($key, $val)
    {
        $trimedKey = trim($key);
        if (strcasecmp($trimedKey, CoreConstants::INTUIT_TID) == 0) {
            $this->intuitTid = trim($val);
        }
    }

    public function getIntuitTid() : string
    {
        return $this->intuitTid;
    }

    private function setContentType($key, $val)
    {
        $trimedKey = trim($key);
        if (strcasecmp($trimedKey, CoreConstants::CONTENT_TYPE) == 0) {
            $this->contentType = trim($val);
        }
    }

    public function getContentType() : string
    {
        return $this->contentType;
    }


    public function setAssociatedRequest(RequestInterface $associatedRequest)
    {
        $this->request = $associatedRequest;
        $this->requestId = $associatedRequest->getRequestId();
        $this->url = $associatedRequest->getUrl();
        return $this;
    }

    public function getAssociatedRequest() : RequestInterface
    {
        return $this->request;
    }

    public function setRequestId(string $requestId){
        $this->requestId = $requestId;
    }

    public function getRequestId() :string {
        return $this->requestId;
    }
}
