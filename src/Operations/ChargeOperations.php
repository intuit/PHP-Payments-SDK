<?php

namespace QuickBooksOnline\Payments\Operations;

use QuickBooksOnline\Payments\Modules\Charge;
use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Request\RequestFactory;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

class ChargeOperations
{
    public static function buildFrom(array $array)
    {
        return new Charge($array);
    }

    /**
     * Create a Charge
     */
    public static function createChargeRequest(Charge $charge, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CHARGE);
        $request->setMethod(RequestInterface::POST)
              ->setUrl($context->getBaseUrl() . EndpointUrls::CHARGE_URL)
              ->setHeader($context->getStandardHeaderWithRequestID($requestId))
              ->setBody(OperationsConverter::getJsonFrom($charge));
        return $request;
    }

    /**
     * Retrieve a Charge by Id
     */
    public static function createGetChargeRequest(string $chargeId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CHARGE);
        $request->setMethod(RequestInterface::GET)
              ->setUrl($context->getBaseUrl() . EndpointUrls::CHARGE_URL . "/" . $chargeId)
              ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }

    /**
     * Capture a Charge by Id
     */
    public static function createCaptureChargeRequest(Charge $charge, string $chargeId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CHARGE);
        $request->setMethod(RequestInterface::POST)
                  ->setUrl($context->getBaseUrl() . EndpointUrls::CHARGE_URL . "/" . $chargeId . "/capture")
                  ->setHeader($context->getStandardHeaderWithRequestID($requestId))
                  ->setBody(OperationsConverter::getJsonFrom($charge));
        return $request;
    }

    /**
      * Refund a Charge
      */
    public static function createRefundChargeRequest(Charge $charge, string $chargeId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CHARGE);
        $request->setMethod(RequestInterface::POST)
                  ->setUrl($context->getBaseUrl() . EndpointUrls::CHARGE_URL . "/" . $chargeId . "/refunds")
                  ->setHeader($context->getStandardHeaderWithRequestID($requestId))
                  ->setBody(OperationsConverter::getJsonFrom($charge));
        return $request;
    }

    /**
     * Get a Refund By ID
     */
    public static function refundBy(string $chargeId, string $refundId, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CHARGE);
        $request->setMethod(RequestInterface::GET)
                ->setUrl($context->getBaseUrl() . EndpointUrls::CHARGE_URL . "/" . $chargeId . "/refunds" . "/" . $refundId)
                ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }

    /**
     * Void a transaction
     */
    public static function voidTransaction(string $chargeRequestId, $requestId, $context)
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CHARGE);
        $request->setMethod(RequestInterface::POST)
            ->setUrl($context->getBaseUrl() . EndpointUrls::VOID_URL . "/" . $chargeRequestId . "/void")
            ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }
}
