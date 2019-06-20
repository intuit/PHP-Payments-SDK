<?php

namespace QuickBooksOnline\Payments\Operations;

use QuickBooksOnline\Payments\Modules\ECheck;
use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Request\RequestFactory;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

class ECheckOperations
{
    public static function buildFrom(array $array)
    {
        return new ECheck($array);
    }

    /**
     * Create a debit
     */
    public static function debit(ECheck $debitBody, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::ECHECK);
        $request->setMethod(RequestInterface::POST)
              ->setUrl($context->getBaseUrl() . EndpointUrls::ECHECK_URL)
              ->setHeader($context->getStandardHeaderWithRequestID($requestId))
              ->setBody(OperationsConverter::getJsonFrom($debitBody));
        return $request;
    }


    /**
     * Retrieve an echeck
     */
    public static function retrieveECheck(string $echeckId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::ECHECK);
        $request->setMethod(RequestInterface::GET)
                  ->setUrl($context->getBaseUrl() . EndpointUrls::ECHECK_URL . "/" . $echeckId)
                  ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }

    /**
     * void or refund an echeck
     */
    public static function voidOrRefundECheck(ECheck $echeck, string $echeckId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::ECHECK);
        $request->setMethod(RequestInterface::POST)
                  ->setUrl($context->getBaseUrl() . EndpointUrls::ECHECK_URL . "/" . $echeckId . "/refunds")
                  ->setHeader($context->getStandardHeaderWithRequestID($requestId))
                  ->setBody(OperationsConverter::getJsonFrom($echeck));
        return $request;
    }

    /**
     * retrieve a Refund
     */
    public static function retrieveRefund(string $echeckId, string $refundId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::ECHECK);
        $request->setMethod(RequestInterface::GET)
                  ->setUrl($context->getBaseUrl() . EndpointUrls::ECHECK_URL . "/" . $echeckId . "/refunds" . "/" . $refundId)
                  ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }
}
