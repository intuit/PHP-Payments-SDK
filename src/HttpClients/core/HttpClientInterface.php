<?php

namespace QuickBooksOnline\Payments\HttpClients\core;

use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface};
use QuickBooksOnline\Payments\HttpClients\Response\{ResponseInterface};

/**
 * A Parent Interface for all the Http Clients
 *
 * @package QuickBooksOnline
 */
 interface HttpClientInterface
 {
     public function send(RequestInterface $request) : ResponseInterface;
     public function getLastSentRequest() : RequestInterface;
     /**
      * Depends on the Framework you are using, you want to either let the SDK
      * do SSL verification for you, or let the framework do it.
      *
      * By default, the HttpClient will check the SSL config for you to make sure everything is
      * working as expected. You will need to use setVerifySSL(false) to disable it.
      */
     public function setVerifySSL(bool $isBuiltInSSLVerifierUsed) : void;
     public function setTimeOut(int $connectionTimeout, int $requestTimeout): void;

     /**
      * Allow User to enable this for debugging purpise.
      */
     public function enableDebug();
     public function getDebugInfo();
 }
