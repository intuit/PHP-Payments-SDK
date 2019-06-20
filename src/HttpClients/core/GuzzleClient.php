<?php

namespace QuickBooksOnline\Payments\HttpClients\core;

use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface};
use QuickBooksOnline\Payments\HttpClients\Response\{IntuitResponse, ResponseInterface, ResponseFactory};

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;


class GuzzleClient implements HttpClientInterface
{
   private $guzzleClient = null;
   //Default 10 seconds
   private $connectionTimeOut;
   //Default 100 seconds
   private $requestTimeOut;
   //Default true;
   private $isVerifySSL;

   /**
    * The last request sent by the client. Regardless success or failure.
    */
   private $lastRequest;

   public function __construct(){
        if(class_exists('GuzzleHttp\Client')){
          $this->guzzleClient = new Client();
          $connectionTimeOut = 10;
          $requestTimeOut = 100;
          $isVerifySSL = true;
        }else{
          throw new \RuntimeException("Cannot find GuzzleHttp client.");
        }
   }

   public function setTimeOut(int $userSetConnectionTimeout, int $userSetRequestTimeout) : void{
       $this->connectionTimeOut = $userSetConnectionTimeout;
       $this->requestTimeOut = $userSetRequestTimeout;
   }

   public function setVerifySSL(bool $isBuiltInSSLVerifierUsed) : void {
       $this->isVerifySSL = $isBuiltInSSLVerifierUsed;
   }

   public function send(RequestInterface $request) : ResponseInterface {
       if(!isset($request)){
           throw new \RuntimeException("Cannot send an empty request.");
       }
       $this->lastRequest = $request;
       $guzzleOptions = $this->prepare($request);
       try{
          $guzzleResponse = $this->guzzleClient->request($request->getMethod(), $request->getUrl(), $guzzleOptions);
       }catch(RequestException $e){
          return $this->createResponseForFailureRequest($e, $request);
       }
       return $this->createResponseForSuccessfulRequest($guzzleResponse, $request);
   }

   private function createResponseForFailureRequest(RequestException $e, RequestInterface $request){
     $body = $e->getResponse()->getBody(true)->getContents();
     $headers = $this->simplifyArrayHeaders($e->getResponse()->getHeaders());
     $statusCode = $e->getResponse()->getStatusCode();
     return ResponseFactory::createStandardIntuitResponse($statusCode, $headers, $body, $request);
   }

   private function createResponseForSuccessfulRequest($guzzleResponse, RequestInterface $request){
     $statusCode = $guzzleResponse->getStatusCode();
     $headers = $this->simplifyArrayHeaders($guzzleResponse->getHeaders());
     $body = $guzzleResponse->getBody(true)->getContents();
     return ResponseFactory::createStandardIntuitResponse($statusCode, $headers, $body, $request);
   }

   private function simplifyArrayHeaders(array $headers){
      $simpleHeader = array();
      foreach($headers as $k => $v){
         $simpleHeader[$k] = $v[0];
      }
      return $simpleHeader;
   }

   private function prepare(RequestInterface $request) {
      $options = array();
      if(strcmp($request->getMethod(), RequestInterface::POST) === 0){ $options['body'] = $request->getBody();}
      if($this->isVerifySSL){  $options['verify'] = CoreConstants::getCertPath();}
      $options['headers'] = $request->getHeader();
      $options['timeout'] = $this->requestTimeOut;
      return $options;
   }

   public function getLastSentRequest() : RequestInterface {
        return $this->lastRequest;
   }

   /**
    * Guzzle Client do not do anything to enable debug.
    */
   public function enableDebug() {}
   public function getDebugInfo() {}

}
