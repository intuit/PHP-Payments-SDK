<?php
namespace QuickBooksOnline\Payments\OAuth;

class DiscoverySandboxURLs extends DiscoveryURLs
{
    public function __construct()
    {
        parent::__construct();
        $this->setUserinfoEndpointUrl("https://sandbox-accounts.platform.intuit.com/v1/openid_connect/userinfo");
        $this->setMigrationEndpointUrl("https://developer-sandbox.api.intuit.com/v2/oauth2/tokens/migrate");
    }
}
