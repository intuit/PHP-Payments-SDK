<?php

namespace QuickBooksOnline\Payments\Operations;

use QuickBooksOnline\Payments\Modules\Card;
use QuickBooksOnline\Payments\Modules\Token;
use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Request\RequestFactory;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

class CardOperations
{
    public static function buildFrom(array $array)
    {
        return new Card($array);
    }

    /**
     * Create a Card
     */
    public static function createCard(Card $card, $customerID, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CARD);
        $request->setMethod(RequestInterface::POST)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/cards")
            ->setHeader($context->getStandardHeaderWithRequestID($requestId))
            ->setBody(OperationsConverter::getJsonFrom($card));
        return $request;
    }

    /**
     * Delete a Customer's Card
     */
    public static function deleteCard($customerID, string $cardId, string $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CARD);
        $request->setMethod(RequestInterface::DELETE)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/cards" . "/" .$cardId)
            ->setHeader($context->getStandardHeaderWithRequestIDForDelete($requestId));
        return $request;
    }

    /**
     * Get all cards associated with a Customer
     */
    public static function getAllCards($customerID, string $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CARD);
        $request->setMethod(RequestInterface::GET)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/cards")
            ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }

    /**
     * Get a card associated with a Customer
     */
    public static function getCard($customerID, string $cardId, string $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CARD);
        $request->setMethod(RequestInterface::GET)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/cards" . "/" . $cardId)
            ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }

    /**
     * Create a Card from Token
     */
    public static function createCardFromToken($customerID, string $tokenValue, $requestId, $context): RequestInterface
    {
        $token = OperationsConverter::createTokenObjFromValue($tokenValue);
        $request = RequestFactory::createStandardIntuitRequest(RequestType::CARD);
        $request->setMethod(RequestInterface::POST)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/cards" . "/createFromToken")
            ->setHeader($context->getStandardHeaderWithRequestID($requestId))
            ->setBody(OperationsConverter::getJsonFrom($token));
        return $request;
    }
}
