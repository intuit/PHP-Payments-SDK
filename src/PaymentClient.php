<?php
namespace QuickBooksOnline\Payments;

use QuickBooksOnline\Payments\Operations\OperationsConverter;
use QuickBooksOnline\Payments\Operations\ChargeOperations;
use QuickBooksOnline\Payments\Operations\CardOperations;
use QuickBooksOnline\Payments\Operations\TokenOperations;
use QuickBooksOnline\Payments\Operations\ECheckOperations;
use QuickBooksOnline\Payments\Operations\BankAccountOperations;
use QuickBooksOnline\Payments\HttpClients\core\ClientFactory;
use QuickBooksOnline\Payments\HttpClients\Response\IntuitResponse;
use QuickBooksOnline\Payments\HttpClients\Response\ResponseInterface;
use QuickBooksOnline\Payments\HttpClients\Response\ResponseFactory;
use QuickBooksOnline\Payments\HttpClients\Request\IntuitRequest;
use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\Modules\BankAccount;
use QuickBooksOnline\Payments\Modules\Charge;
use QuickBooksOnline\Payments\Modules\Card;
use QuickBooksOnline\Payments\Modules\ECheck;
use QuickBooksOnline\Payments\Modules\Token;
use QuickBooksOnline\Payments\Interceptors\InterceptorInterface;

class PaymentClient
{

    /**
     * The Http context for the client.
     */
    private $context;

    /**
     * A list of interceptors to be used in the client AFTER the request has been sent.
     * The $afterRequestinterceptors will not m
     */
    private $afterRequestinterceptors;

    /**
     * For OAuth 2.0 protocol related operations
     */
    private $oauth2Authenticator;

    /**
     * The client that sends the request
     */
    private $httpClient;

    /**
     * PaymentClient interceptors
     *
     * @variable array
     */
    private $interceptors;


    public function __construct(array $context = null)
    {
        if(isset($context) && !empty($context)){
            $this->context = new ClientContext($context);
        }else{
            $this->context = new ClientContext();
        }
        $this->httpClient = ClientFactory::buildCurlClient(
            isset($context['connection_time_out']) ? $context['connection_time_out'] : 10,
            isset($context['request_time_out']) ? $context['request_time_out'] : 100,
            isset($context['is_verify']) ? $context['is_verify'] : false
        );

        $this->interceptors = array();
    }

    /**
     * A generic function to send any request that implements RequestInterface
     * @param RequestInterface $request The request to be sent
     * @param InterceptorInterface $interceptor for this request/response. Optional.
     */
    public function send(RequestInterface $request, InterceptorInterface $interceptor = null) : ResponseInterface
    {
        if (isset($interceptor)) {
            $interceptor->before($request, $this);
            $response = $this->httpClient->send($request);
            $interceptor->after($response, $this);
            $interceptor->intercept($request, $response, $this);
            OperationsConverter::updateResponseBodyToObj($response);
            return $response;
        } else {
            $response = $this->httpClient->send($request);
            OperationsConverter::updateResponseBodyToObj($response);
            return $response;
        }
    }

    public function charge(Charge $charge, string $requestId = "") : ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ChargeOperations::createChargeRequest($charge, $requestId, $this->getContext());

        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function voidChargeTransaction(string $chargeRequestId, string $requestId = "") : ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ChargeOperations::voidTransaction($chargeRequestId, $requestId, $this->getContext());

        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function retrieveCharge(string $chargeId, string $requestId = "") : ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ChargeOperations::createGetChargeRequest($chargeId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function captureCharge(Charge $charge, string $chargeId, string $requestId = "") : ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ChargeOperations::createCaptureChargeRequest($charge, $chargeId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function refundCharge(Charge $charge, string $chargeId, string $requestId = "") : ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ChargeOperations::createRefundChargeRequest($charge, $chargeId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function getRefundDetail(string $chargeId, string $refundId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ChargeOperations::refundBy($chargeId, $refundId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function createCard(Card $card, $customerID, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = CardOperations::createCard($card, $customerID, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function getCard($customerID, string $cardId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = CardOperations::getCard($customerID, $cardId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function deleteCard($customerID, string $cardId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = CardOperations::deleteCard($customerID, $cardId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function getAllCardsFor($customerID, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = CardOperations::getAllCards($customerID, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }


    public function createCardFromToken($customerID, string $tokenValue, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = CardOperations::createCardFromToken($customerID, $tokenValue, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }


    public function createToken($body, bool $isIE = false, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = TokenOperations::createToken($body, $isIE, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }


    public function debit(ECheck $debitBody, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ECheckOperations::debit($debitBody, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function retrieveECheck(string $echeckId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ECheckOperations::retrieveECheck($echeckId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function voidOrRefundEcheck(ECheck $echeck, string $echeckId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ECheckOperations::voidOrRefundEcheck($echeck, $echeckId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function retrieveRefund(string $echeckId, string $refundId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = ECheckOperations::retrieveRefund($echeckId, $refundId, $requestId, $this->getContext());

        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function createBankAccount(BankAccount $account, $customerID, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = BankAccountOperations::createBankAccount($account, $customerID, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function createBankAccountFromToken($customerID, string $tokenValue, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = BankAccountOperations::createBankAccountFromToken($customerID, $tokenValue, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function deleteBankAccount($customerID, string $bankAccountId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = BankAccountOperations::deleteBankAccount($customerID, $bankAccountId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    public function getAllBankAccount($customerID, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = BankAccountOperations::getAllbankAccountsFor($customerID, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }


    public function getBankAccount($customerID, string $bankAccountId, string $requestId = ""): ResponseInterface
    {
        if (empty($requestId)) {
            $requestId = ClientContext::generateRequestID();
        }
        $request = BankAccountOperations::getBankAccountFor($customerID, $bankAccountId, $requestId, $this->getContext());
        $this->before($request, $this);
        $response = $this->httpClient->send($request);
        $this->after($response, $this);
        $this->intercept($request, $response);
        OperationsConverter::updateResponseBodyToObj($response);
        return $response;
    }

    private function intercept(RequestInterface $request, ResponseInterface $response)
    {
        $interceptors = $this->interceptors;
        foreach ($this->interceptors as $interceptor) {
            $interceptor->intercept($request, $response, $this);
        }
    }

    private function before(RequestInterface &$request, PaymentClient $client)
    {
        $interceptors = $this->interceptors;
        foreach ($this->interceptors as $interceptor) {
            $interceptor->before($request, $client);
        }
    }

    private function after(ResponseInterface &$response, PaymentClient $client)
    {
        $interceptors = $this->interceptors;
        foreach ($this->interceptors as $interceptor) {
            $interceptor->after($response, $client);
        }
    }

    public function getUrl()
    {
        return $this->context->getBaseUrl();
    }

    /**
    * Set the URL for the API. It is either https://sandbox.api.intuit.com or
    *  "https://api.intuit.com"
    */
    public function setUrl(string $url)
    {
        if (!isset($url) || is_empty($url)) {
            throw new \RuntimeException("Set empty base url for Payments API.");
        }
        $this->context->setBaseUrl($url);
        return $this;
    }

    /**
     * Get the value of Access Token
     *
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->context->getAccessToken();
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
        $this->context->setAccessToken($accessToken);

        return $this;
    }

    /**
     * Get the value of Refresh Token
     *
     * @return mixed
     */
    public function getRefreshToken()
    {
        return $this->context->getRefreshToken();
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
        $this->context->setRefreshToken($refreshToken);

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
        $this->context->setenvironment($environment);
    }

    /**
     * Get the value of A list of interceptors to be used in the client
     *
     * @return mixed
     */
    public function getAllInterceptors()
    {
        return $this->interceptors;
    }

    public function getInterceptor(string $interceptorName)
    {
        if (array_key_exists($interceptorName, $this->interceptors)) {
            return $this->interceptors[$interceptorName];
        } else {
            return null;
        }
    }

    /**
     * @param string $name
     * @param InterceptorInterface
     */
    public function addInterceptor($name, $interceptor)
    {
        $theInterceptor = $this->getInterceptor($name);
        if (!isset($theInterceptor)) {
            $this->interceptors[$name] = $interceptor;
        } else {
            throw new \RuntimeException("Interceptor with name: " . $name . " already exists.");
        }
        return $this;
    }

    public function removeInterceptor($name)
    {
        if (array_key_exists($name, $this->interceptors)) {
            unset($this->interceptors[$name]);
        } else {
            throw new \RuntimeException("Interceptor with name: " . $name . " cannot be deleted. It does not exist.");
        }
    }

    /**
     * Get the value of Oauth Authenticator
     *
     * @return mixed
     */
    public function getOauth2Authenticator()
    {
        return $this->oauth2Authenticator;
    }

    /**
     * Set the value of Oauth Authenticator
     *
     * @param mixed oauth2Authenticator
     *
     * @return self
     */
    public function setOauth2Authenticator($oauth2Authenticator)
    {
        $this->oauth2Authenticator = $oauth2Authenticator;

        return $this;
    }

    /**
     * Get the value of Http Client
     *
     * @return mixed
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Set the value of Http Client
     *
     * @param mixed httpClient
     *
     * @return self
     */
    public function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Get the value of The Http context for the client.
     *
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set the value of The Http context for the client.
     *
     * @param mixed context
     *
     * @return self
     */
    public function setContext(ClientContext $context)
    {
        $this->context = $context;

        return $this;
    }
}
