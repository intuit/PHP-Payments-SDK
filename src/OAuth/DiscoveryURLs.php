<?php

namespace QuickBooksOnline\Payments\OAuth;

class DiscoveryURLs
{
    private $issuer_url;
    private $authorization_endpoint_url;
    private $token_endpoint_url;
    private $userinfo_endpoint_url;
    private $revocation_endpoint_url;
    private $migration_endpoint_url;

    public function __construct()
    {
        $this->setIssuerUrl("https://oauth.platform.intuit.com/op/v1");
        $this->setAuthorizationEndpointUrl("https://appcenter.intuit.com/connect/oauth2");
        $this->setTokenEndpointUrl("https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer");
        $this->setUserinfoEndpointUrl("https://accounts.platform.intuit.com/v1/openid_connect/userinfo");
        $this->setRevocationEndpointUrl("https://developer.api.intuit.com/v2/oauth2/tokens/revoke");
        $this->setMigrationEndpointUrl("https://developer.api.intuit.com/v2/oauth2/tokens/migrate");
    }

    /**
     * Get the value of Issuer Url
     *
     * @return mixed
     */
    public function getIssuerUrl()
    {
        return $this->issuer_url;
    }

    /**
     * Set the value of Issuer Url
     *
     * @param mixed issuer_url
     *
     * @return self
     */
    public function setIssuerUrl($issuer_url)
    {
        $this->issuer_url = $issuer_url;

        return $this;
    }

    /**
     * Get the value of Authorization Endpoint Url
     *
     * @return mixed
     */
    public function getAuthorizationEndpointUrl()
    {
        return $this->authorization_endpoint_url;
    }

    /**
     * Set the value of Authorization Endpoint Url
     *
     * @param mixed authorization_endpoint_url
     *
     * @return self
     */
    public function setAuthorizationEndpointUrl($authorization_endpoint_url)
    {
        $this->authorization_endpoint_url = $authorization_endpoint_url;

        return $this;
    }

    /**
     * Get the value of Token Endpoint Url
     *
     * @return mixed
     */
    public function getTokenEndpointUrl()
    {
        return $this->token_endpoint_url;
    }

    /**
     * Set the value of Token Endpoint Url
     *
     * @param mixed token_endpoint_url
     *
     * @return self
     */
    public function setTokenEndpointUrl($token_endpoint_url)
    {
        $this->token_endpoint_url = $token_endpoint_url;

        return $this;
    }

    /**
     * Get the value of Userinfo Endpoint Url
     *
     * @return mixed
     */
    public function getUserinfoEndpointUrl()
    {
        return $this->userinfo_endpoint_url;
    }

    /**
     * Set the value of Userinfo Endpoint Url
     *
     * @param mixed userinfo_endpoint_url
     *
     * @return self
     */
    public function setUserinfoEndpointUrl($userinfo_endpoint_url)
    {
        $this->userinfo_endpoint_url = $userinfo_endpoint_url;

        return $this;
    }

    /**
     * Get the value of Revocation Endpoint Url
     *
     * @return mixed
     */
    public function getRevocationEndpointUrl()
    {
        return $this->revocation_endpoint_url;
    }

    /**
     * Set the value of Revocation Endpoint Url
     *
     * @param mixed revocation_endpoint_url
     *
     * @return self
     */
    public function setRevocationEndpointUrl($revocation_endpoint_url)
    {
        $this->revocation_endpoint_url = $revocation_endpoint_url;

        return $this;
    }


    /**
     * Get the value of Migration Endpoint Url
     *
     * @return mixed
     */
    public function getMigrationEndpointUrl()
    {
        return $this->migration_endpoint_url;
    }

    /**
     * Set the value of Migration Endpoint Url
     *
     * @param mixed migration_endpoint_url
     *
     * @return self
     */
    public function setMigrationEndpointUrl($migration_endpoint_url)
    {
        $this->migration_endpoint_url = $migration_endpoint_url;

        return $this;
    }
}
