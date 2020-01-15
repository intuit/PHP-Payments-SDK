<?php
namespace QuickBooksOnline\Payments\HttpClients\core;

use QuickBooksOnline\Payments\HttpClients\core\HttpCurlClient;

class ClientFactory
{
    public static function buildCurlClient(
        int $connectionTimeOut = 10,
        int $requestTimeOut = 100,
        bool $isVerify = false
    ) {
        $client =  new HttpCurlClient();
        $client->setVerifySSL($isVerify);
        $client->setTimeOut($connectionTimeOut, $requestTimeOut);
        return $client;
    }

    public static function buildGuzzleClient(
        int $connectionTimeOut = 10,
        int $requestTimeOut = 100,
        bool $isVerify = false
    ) {
        $client =  new GuzzleClient();
        $client->setVerifySSL($isVerify);
        $client->setTimeOut($connectionTimeOut, $requestTimeOut);
        return $client;
    }
}
