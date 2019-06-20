<?php

namespace QuickBooksOnline\Payments\Operations;

use QuickBooksOnline\Payments\Modules\BankAccount;
use QuickBooksOnline\Payments\Modules\Token;
use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Request\RequestFactory;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

class BankAccountOperations
{
    public static function buildFrom(array $array)
    {
        return new BankAccount($array);
    }

    /**
     * Create a Bank Account
     */
    public static function createBankAccount(BankAccount $bankaccount, $customerID, string $requestId, $context) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::BANKACCOUNT);
        $request->setMethod(RequestInterface::POST)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/bank-accounts")
            ->setHeader($context->getStandardHeaderWithRequestID($requestId))
            ->setBody(OperationsConverter::getJsonFrom($bankaccount));
        return $request;
    }

    /**
     * Create a Bank Account from Token
     */
    public static function createBankAccountFromToken($customerID, string $tokenValue, string $requestId, $context) : RequestInterface
    {
        $token = OperationsConverter::createTokenObjFromValue($tokenValue);
        $request = RequestFactory::createStandardIntuitRequest(RequestType::BANKACCOUNT);
        $request->setMethod(RequestInterface::POST)
            ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/bank-accounts" . "/createFromToken")
            ->setHeader($context->getStandardHeaderWithRequestID($requestId))
            ->setBody(OperationsConverter::getJsonFrom($token));
        return $request;
    }

    /**
     * Delete a Customer's bankAccount
     */
    public static function deleteBankAccount($customerID, string $bankAccountId, string $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::BANKACCOUNT);
        $request->setMethod(RequestInterface::DELETE)
              ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/bank-accounts" . "/" .$bankAccountId)
              ->setHeader($context->getStandardHeaderWithRequestIDForDelete($requestId));
        return $request;
    }

    /**
     * Get all banks for a Customer
     */
    public static function getAllbankAccountsFor($customerID, string $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::BANKACCOUNT);
        $request->setMethod(RequestInterface::GET)
              ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/bank-accounts")
              ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }

    /**
     * Get a Bank Account
     */
    public static function getBankAccountFor($customerID, string $bankAccountId, string $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::BANKACCOUNT);
        $request->setMethod(RequestInterface::GET)
              ->setUrl($context->getBaseUrl() . EndpointUrls::CUSTOMER_URL . "/" . $customerID . "/bank-accounts" . "/" .$bankAccountId)
              ->setHeader($context->getStandardHeaderWithRequestID($requestId));
        return $request;
    }
}
