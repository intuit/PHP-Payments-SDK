<?php
namespace QuickBooksOnline\Payments;

use QuickBooksOnline\Payments\Operations\OperationsConverter;

class ClientContext
{
    const SANDBOX_URL = "https://sandbox.api.intuit.com";
    const PRODUCTION_URL = "https://api.intuit.com";

    private $accessToken;
    private $refreshToken;
    private $environment;
    private $baseUrl;

    public function __construct(array $context = null){
        if(isset($context) && !empty($context)){
             $this->setAccessToken( array_key_exists('access_token', $context) ? $context['access_token'] : null);
             $this->setRefreshToken( array_key_exists('refresh_token', $context) ? $context['refresh_token'] : null);
             $this->setenvironment ( array_key_exists('environment', $context) ? $context['environment'] : "");
        }
    }

    /**
     * Auto generate a 20 charactor length request ID
     */
    public static function generateRequestID() : string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }


    public function getStandardHeaderWithRequestID(string $requestId) : array
    {
        return array(
             'Accept' => 'application/json',
             'Content-Type' => 'application/json',
             'Request-Id' => $requestId,
             'Authorization' => "Bearer " . $this->accessToken
           );
    }

    public function getStandardHeaderWithRequestIDForDelete(string $requestId) : array
    {
        return array(
             'Content-Type' => 'application/json',
             'Request-Id' => $requestId,
             'Authorization' => "Bearer " . $this->accessToken
           );
    }

    public function getNonAuthHeaderWithRequestID(string $requestId) : array
    {
        return array(
             'Accept' => 'application/json',
             'Request-Id' => $requestId,
             'Content-Type' => 'application/json'
           );
    }

    /**
     * Get the value of Access Token
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set the value of Access Token
     *
     * @param mixed accessToken
     *
     * @return self
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get the value of Refresh Token
     *
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Set the value of Refresh Token
     *
     * @param mixed refreshToken
     *
     * @return self
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }


    /**
     * Set the value of environment
     *
     * @param mixed environment
     *
     * @return self
     */
    public function setenvironment($environment)
    {
        $env = strtolower($environment);
        if (substr($env, 0, strlen("prod")) === "prod") {
            $this->baseUrl =  ClientContext::PRODUCTION_URL;
        } else {
            $this->baseUrl =  ClientContext::SANDBOX_URL;
        }
        return $this;
    }

    /**
     * Get the value of Base Url
     *
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * Set the value of Base Url
     *
     * @param mixed baseUrl
     *
     * @return self
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }
}
