<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Operations\ChargeOperations;
use QuickBooksOnline\Payments\OAuth\{DiscoverySandboxURLs, DiscoveryURLs, OAuth2Authenticator, OAuth1Encrypter};
use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface, IntuitRequest, RequestFactory};

final class OAuth2Test extends TestCase
{
  private function createClient() : OAuth2Authenticator {
    $oauth2Helper = OAuth2Authenticator::create([
      'client_id' => 'L0vmMZIfwUBfv9PPM96dzMTYATnLs6TSAe5SyVkt1Z4MAsvlCU',
      'client_secret' => '2ZZnCnnDyoZxUlVCP1D9X7khxA3zuXMyJE4cHXdq',
      'redirect_uri' => 'https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl',
      'environment' => 'development'
    ]);
    return $oauth2Helper;
  }

  public function testCanCreateAnOAuth2HelperWithValues(): void
  {
      $oauth2Helper = $this->createClient();
      $this->assertEquals(
          'L0vmMZIfwUBfv9PPM96dzMTYATnLs6TSAe5SyVkt1Z4MAsvlCU',
          $oauth2Helper->getClientId()
      );

      $this->assertEquals(
          '2ZZnCnnDyoZxUlVCP1D9X7khxA3zuXMyJE4cHXdq',
          $oauth2Helper->getClientSecret()
      );

      $this->assertEquals(
          'https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl',
          $oauth2Helper->getRedirectUri()
      );

      $this->assertInstanceOf(
          DiscoverySandboxURLs::class,
          $oauth2Helper->getDiscoveryURLs()
      );
  }

  public function testIfCorrectDiscvoeryURLsLoad() : void {
    $oauth2Helper = $this->createClient();

    $dicoveryURL = $oauth2Helper->getDiscoveryURLs();
    $this->assertEquals(
        "https://sandbox-accounts.platform.intuit.com/v1/openid_connect/userinfo",
        $dicoveryURL->getUserinfoEndpointUrl()
    );
  }

  public function testIfStateCanBeChanged() : void {
    $oauth2Helper = $this->createClient();
    $this->assertNull(
        $oauth2Helper->getState()
    );

    $scope = "com.intuit.quickbooks.accounting openid profile email phone address";

    $authorizationCodeURL = $oauth2Helper->generateAuthCodeURL($scope);
    $this->assertNotNull(
        $oauth2Helper->getState()
    );


    $oauth2Helper-> setState("intuit");
    $authorizationCodeURL = $oauth2Helper->generateAuthCodeURL($scope);
    $this->assertEquals(
        "intuit",
        $oauth2Helper->getState()
    );

    $authorizationCodeURL = $oauth2Helper->generateAuthCodeURL($scope, "turbo");
    $this->assertEquals(
        "turbo",
        $oauth2Helper->getState()
    );
  }

  public function testGenerateAuthorizationCodeURL() : void
  {
    $oauth2Helper = $this->createClient();

    $scope = "com.intuit.quickbooks.accounting openid profile email phone address";
    $state = "aState" . rand();

    $authorizationCodeURL = $oauth2Helper->generateAuthCodeURL($scope, $state);
    $this->assertEquals(
      "https://appcenter.intuit.com/connect/oauth2?client_id=L0vmMZIfwUBfv9PPM96dzMTYATnLs6TSAe5SyVkt1Z4MAsvlCU&scope=com.intuit.quickbooks.accounting%20openid%20profile%20email%20phone%20address&redirect_uri=" . "https%3A%2F%2Fdeveloper.intuit.com%2Fv2%2FOAuth2Playground%2FRedirectUrl" . "&response_type=code&state=" . $state,
      $authorizationCodeURL
    );
  }

  public function testIfExchangeCodeForTokensIsCorrect() : void
  {
    $oauth2Helper = $this->createClient();
    $code = "someCode";
    $request = $oauth2Helper->createRequestToExchange($code);

    $this->assertInstanceOf(
        RequestInterface::class,
        $request
    );

    $body = $request->getBody();
    $this->assertIsString(
        $body
    );
    $array = explode('&', $body);
    $setCode = explode('=',$array[1])[1];

    $this->assertEquals(
      $code,
      $setCode
    );

    $authorizationHeader = "Basic TDB2bU1aSWZ3VUJmdjlQUE05NmR6TVRZQVRuTHM2VFNBZTVTeVZrdDFaNE1Bc3ZsQ1U6MlpabkNubkR5b1p4VWxWQ1AxRDlYN2toeEEzenVYTXlKRTRjSFhkcQ==";
    $header = $request->getHeader();
    $this->assertIsArray(
        $header
    );

    $generatedAuthorizationHeader = $header['Authorization'];
    $this->assertEquals(
      $authorizationHeader,
      $generatedAuthorizationHeader
    );
  }

  public function testCanRequestRefreshToken() : void
  {
    $oauth2Helper = $this->createClient();
    $refreshToken = "someRefreshToken";
    $request = $oauth2Helper->createRequestToRefresh($refreshToken);

    $this->assertInstanceOf(
        RequestInterface::class,
        $request
    );
    $body = $request->getBody();

    $this->assertIsString($body);

    $bodyArray = explode('&', $body);
    $refreshTokenGenerated = explode('=', $bodyArray[1]);

    $this->assertEquals(
      $refreshTokenGenerated[1],
      $refreshToken
    );
  }

  public function testCanRevoke() : void {
    $oauth2Helper = $this->createClient();
    $refreshToken = "someRefreshToken";
    $request = $oauth2Helper->createRequestToRevoke($refreshToken);

    $body = json_encode(array( "token" => $refreshToken));
    $acturalBody = $request->getBody();


    $this->assertEquals(
      $acturalBody,
      $body
    );
  }

  public function testGetUserInfo() : void {
    $oauth2Helper = $this->createClient();
    $accessToken = "accessToken";
    $request = $oauth2Helper->createRequestForUserInfo($accessToken);

    $this->assertInstanceOf(
        RequestInterface::class,
        $request
    );

    $header = $request->getHeader();

    $this->assertEquals(
      $header['Authorization'],
      "Bearer " . $accessToken
    );
  }

  public function testOAuth1SigGeneratorSignCorrectly() : void {
    $consumerKey= "qyprdUSoVpIHrtBp0eDMTHGz8UXuSz";
    $consumerSecret = "TKKBfdlU1I1GEqB9P3AZlybdC8YxW5qFSbuShkG7";
    $oauth1AccessToken = "qyprd5jgTqKPpZvNUM5OOLDEoPthaUnYRkDGP5o8Z4vmbUx5";
    $oauth1TokenSecret = "kFKirS5qfbj1j5naG2eRiHMROwsAS1AhW4aNweI1";
    $scopes = "com.intuit.quickbooks.accounting";
    $encrypter = new OAuth1Encrypter($consumerKey, $consumerSecret, $oauth1AccessToken, $oauth1TokenSecret);
    $encrypter->setNounceForTest("NjM2OTI4NDM3NDQzNjQyNDUw");
    $encrypter->setTimeForTest("1557272144");
    $authorizationHeaderInfo = $encrypter->getOAuthHeader("https://developer-sandbox.api.intuit.com/v2/oauth2/tokens/migrate", array(), RequestInterface::POST);
    $array = explode(",", $authorizationHeaderInfo);
    $sig = explode("=", $array[6]);
    $result = $sig[1];
    $result = str_replace('"', "", $result);
    $result = str_replace("'", "", $result);
    $this->assertEquals(
      $result,
      "hbXtg1Foug2WKXEu%2Fz1lRpe5rbk%3D"
    );
  }

}
