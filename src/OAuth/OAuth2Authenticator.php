<?php
namespace QuickBooksOnline\Payments\OAuth;

use QuickBooksOnline\Payments\HttpClients\Request\RequestInterface;
use QuickBooksOnline\Payments\HttpClients\Request\RequestFactory;
use QuickBooksOnline\Payments\HttpClients\Request\IntuitRequest;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

use \InvalidArgumentException;

class OAuth2Authenticator
{
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $discoveryURLs;

    private $state;
    private $scope;


    private function __construct($settings)
    {
        $this->setKeysFromMap($settings);
    }

    public static function create($settings)
    {
        if (isset($settings)) {
            return new self($settings);
        } else {
            throw new InvalidArgumentException("Empty OAuth keys");
        }
    }

    private function setKeysFromMap(array $settings)
    {
        try {
            $this->setClientId($settings['client_id']);
            $this->setClientSecret($settings['client_secret']);
            $this->setRedirectUri($settings['redirect_uri']);
            $this->setEnvironment($settings['environment']);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Fail reading OAuth 2 keys from :" + $settings);
        }
    }

    private function setEnvironment(string $environment)
    {
        $env = strtolower($environment);
        if (substr($env, 0, strlen("prod")) === "prod") {
            $this->setDiscoveryURLs(new DiscoveryURLs());
        } else {
            $this->setDiscoveryURLs(new DiscoverySandboxURLs());
        }
    }


    /**
     * Return the AuthorizationCode URL. Developers need to redirect Users to this URL to start OAuth 2.0.
     * @return string AuthorizationCodeURL;
     */
    public function generateAuthCodeURL(string $scope, string $userDefinedState = "state") : string
    {
        if ($this->isUserPassState($userDefinedState)) {
            $this->setState($userDefinedState);
        } else {
            $this->generateStateIfNotSet();
        }
        $this->setScope($scope);

        return $this->getDiscoveryURLs()->getAuthorizationEndpointUrl() .
                      "?" . $this->generateQueryParemeterStringForAuthorizationCodeURL();
    }

    /**
     *  Create a request to excange code for OAuth 2 token.
     *
     *  @return RequestInterface authorizateCodeForTokenRequest
     */
    public function createRequestToExchange(string $code) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::OAUTH);
        $request->setMethod(RequestInterface::POST)
               ->setUrl($this->getDiscoveryURLs()->getTokenEndpointUrl())
               ->setHeader($this->generateHeaderForTokenRequest())
               ->setBody($this->generateBodyForTokenRequest($code));
        return $request;
    }

    /**
     *  Create a request to excange code for OAuth 2 token.
     *
     *  @return RequestInterface refreshTokenRequest
     */
    public function createRequestToRefresh(string $refreshToken) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::OAUTH);
        $request->setMethod(RequestInterface::POST)
               ->setUrl($this->getDiscoveryURLs()->getTokenEndpointUrl())
               ->setHeader($this->refreshTokenHeader())
               ->setBody($this->refreshTokenBody($refreshToken));
        return $request;
    }

    /**
     *  Create a request to revoken OAuth 2.0 token
     *
     *  @return RequestInterface revokeTokenRequest
     */
    public function createRequestToRevoke(string $token) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::OAUTH);
        $request->setMethod(RequestInterface::POST)
               ->setUrl($this->getDiscoveryURLs()->getRevocationEndpointUrl())
               ->setHeader($this->revokeTokenHeader())
               ->setBody($this->revokeTokenBody($token));
        return $request;
    }

    /**
     *  Create a request to revoken OAuth 2.0 token
     *
     *  @return RequestInterface getUserInfoRequest
     */
    public function createRequestForUserInfo(string $accessToken) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::USERINFO);
        $request->setMethod(RequestInterface::GET)
               ->setUrl($this->getDiscoveryURLs()->getUserinfoEndpointUrl())
               ->setHeader($this->userInfoHeader($accessToken));
        return $request;
    }

    /**
     *  Create a request to migrate OAuth 2.0 token
     *
     *  @return RequestInterface migrateTokenRequest
     */
    public function createRequestToMigrateToken(string $consumerKey, string $consumerSecret, string $oauth1AccessToken, string $oauth1TokenSecret, string $scopes) : RequestInterface
    {
        $request = RequestFactory::createStandardIntuitRequest(RequestType::OAUTH);
        $oauth1Encrypter = new OAuth1Encrypter($consumerKey, $consumerSecret, $oauth1AccessToken, $oauth1TokenSecret);
        $request->setMethod(RequestInterface::POST)
               ->setUrl($this->getDiscoveryURLs()->getMigrationEndpointUrl())
               ->setHeader($this->migrationAuthorizationHeader($oauth1Encrypter, $this->getDiscoveryURLs()->getMigrationEndpointUrl()))
               ->setBody($this->migrationBody($scopes));
        return $request;
    }

    private function migrationAuthorizationHeader($oauth1Encrypter, $baseURL)
    {
        $authorizationHeaderInfo = $oauth1Encrypter->getOAuthHeader($baseURL, array(), RequestInterface::POST);
        return array(
          'Accept' => 'application/json',
          'Authorization' => $authorizationHeaderInfo,
          'Content-Type' => 'application/json'
        );
    }

    private function migrationBody($scope)
    {
        $body = array(
          'scope' => $scope,
          'redirect_uri' => "https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl",
          'client_id' => $this->getClientId(),
          'client_secret' => $this->getClientSecret()
        );
        return json_encode($body);
    }



    private function userInfoHeader($accessToken)
    {
        return array(
           'Accept' => 'application/json',
           'Authorization' => "Bearer " . $accessToken
         );
    }

    private function revokeTokenHeader()
    {
        return array(
           'Accept' => 'application/json',
           'Authorization' => $this->generateAuthorizationHeader(),
           'Content-Type' => 'application/json'
         );
    }

    private function revokeTokenBody($token)
    {
        $body = array(
       "token" => $token
     );
        return json_encode($body);
    }

    private function refreshTokenHeader() : array
    {
        return array(
            'Accept' => "application/json",
            'Authorization' => $this->generateAuthorizationHeader(),
            'Content-Type' => "application/x-www-form-urlencoded",
        );
    }

    private function refreshTokenBody($refreshToken) : string
    {
        $body =  array(
          'grant_type' => 'refresh_token',
          'refresh_token' => (String)$refreshToken
        );
        return http_build_query($body);
    }

    private function generateBodyForTokenRequest($code)
    {
        $body =  array(
            'grant_type' => 'authorization_code',
            'code' => (String)$code,
            'redirect_uri' => $this->getRedirectUri()
         );
        return http_build_query($body);
    }

    private function generateHeaderForTokenRequest()
    {
        return array(
         'Accept' => 'application/json',
         'Authorization' => $this->generateAuthorizationHeader(),
         'Content-Type' => 'application/x-www-form-urlencoded'
       );
    }

    private function generateAuthorizationHeader()
    {
        $encodedClientIDClientSecrets = base64_encode($this->getClientId() . ':' . $this->getClientSecret());
        $authorizationheader = "Basic " . $encodedClientIDClientSecrets;
        return $authorizationheader;
    }


    private function isUserPassState(string $state) : bool
    {
        if (isset($state) && strcmp($state, "state") === 0) {
            return false;
        } else {
            return true;
        }
    }

    private function generateStateIfNotSet()
    {
        $currentState = $this->getState();
        if (!isset($currentState)) {
            $tmpState =  chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
            $this->setState($tmpState);
        }
    }

    private function generateQueryParemeterStringForAuthorizationCodeURL() : string
    {
        $parameters = array(
          'client_id' => $this->getClientId(),
          'scope' => $this->getScope(),
          'redirect_uri' => $this->getRedirectUri(),
          'response_type' => 'code',
          'state' => $this->getState()
      );
        return http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Get the value of Client Id
     *
     * @return mixed
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set the value of Client Id
     *
     * @param mixed client_id
     *
     * @return self
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;

        return $this;
    }

    /**
     * Get the value of Client Secret
     *
     * @return mixed
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * Set the value of Client Secret
     *
     * @param mixed client_secret
     *
     * @return self
     */
    public function setClientSecret($client_secret)
    {
        $this->client_secret = $client_secret;

        return $this;
    }

    /**
     * Get the value of Redirect Uri
     *
     * @return mixed
     */
    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    /**
     * Set the value of Redirect Uri
     *
     * @param mixed redirect_uri
     *
     * @return self
     */
    public function setRedirectUri($redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;

        return $this;
    }


    /**
     * Get the value of State
     *
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get the value of Scope
     *
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set the value of Scope
     *
     * @param mixed scope
     *
     * @return self
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }


    /**
     * Get the value of Discovery Ls
     *
     * @return mixed
     */
    public function getDiscoveryURLs()
    {
        return $this->discoveryURLs;
    }

    /**
     * Set the value of Discovery Ls
     *
     * @param mixed discoveryURLs
     *
     * @return self
     */
    public function setDiscoveryURLs(DiscoveryURLs $discoveryURLs)
    {
        $this->discoveryURLs = $discoveryURLs;

        return $this;
    }
}
