<?php

namespace QuickBooksOnline\Payments\Operations;

use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Request\RequestFactory;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;
use QuickBooksOnline\Payments\Modules\Card;
use QuickBooksOnline\Payments\Modules\BankAccount;
use QuickBooksOnline\Payments\Modules\Token;

class TokenOperations
{

    /**
     * Create a Token
     * @param mixed bankAccount or Card to exchange for token.
     */
    public static function createToken($tokenBody, bool $isIE, $requestId, $context): RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::TOKEN);
        $requestBody = array();
        if ($tokenBody instanceof Card) {
            $requestBody['card'] = $tokenBody;
        } elseif ($tokenBody instanceof BankAccount) {
            $requestBody['bankAccount'] = $tokenBody;
        }
        $url = $context->getBaseUrl() . ($isIE ? EndpointUrls::TOKEN_URL_IE : EndpointUrls::TOKEN_URL);
        $request->setMethod(RequestInterface::POST)
              ->setUrl($url)
              ->setHeader($context->getNonAuthHeaderWithRequestID($requestId))
              ->setBody(OperationsConverter::getJsonFrom($requestBody));
        return $request;
    }
}
