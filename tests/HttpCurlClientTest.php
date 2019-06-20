<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\OAuth\{DiscoverySandboxURLs, DiscoveryURLs, OAuth2Authenticator, OAuth1Encrypter};
use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface, IntuitRequest, RequestFactory};
use QuickBooksOnline\Payments\HttpClients\core\{HttpClientInterface, HttpCurlClient};

final class HttpCurlClientTest extends TestCase
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

  private function enableDebugForCurl($request, $isVerifySSL){
    $curlClient = new HttpCurlClient();
    $curlClient->setVerifySSL($isVerifySSL);
    $baseCurl = $curlClient->enableDebug();
    $response = $curlClient->send($request);
    $information = $curlClient->getDebugInfo();
    $array = explode(PHP_EOL, $information['request_header']);
    foreach($array as $k => $v){
       if(strpos($v, 'Authorization') !== false){
          $authorizationValue = explode(":", $v);
          $this->assertEquals(
              $request->getHeader()['Authorization'],
              trim($authorizationValue['1'])
          );
       }
    }

    $curlUrl = $information['url'];
    $this->assertEquals(
        $request->getUrl(),
        $curlUrl
    );

    $sslVerifyResult = $information['ssl_verify_result'];
    $this->assertEquals(
        $sslVerifyResult,
        0
    );
  }

  public function testExchangeCodeRequestSentByCurlClient(): void
  {
      $oauth2Helper = $this->createClient();
      $code = "L011557358660z3axu8cgM7YHVyRGAaU63Ap0hgtEzfdkgwu5d";
      $request = $oauth2Helper->createRequestToExchange($code);
      $this->enableDebugForCurl($request, true);
  }

  public function testRefreshTokenRequestSentByCurlClient(): void
  {
      $oauth2Helper = $this->createClient();
      $token = "refreshToken";
      $request = $oauth2Helper->createRequestToRefresh($token);
      $this->enableDebugForCurl($request, true);
  }

  public function testUserInfoRequestSentByCurlClient(): void
  {
      $oauth2Helper = $this->createClient();
      $token = "accessToken";
      $request = $oauth2Helper->createRequestForUserInfo($token);
      $this->enableDebugForCurl($request, true);
  }

  public function testMigrateRequestSentByCurlClient(): void
  {
      $oauth2Helper = $this->createClient();
      $consumerKey= "qyprdUSoVpIHrtBp0eDMTHGz8UXuSz";
      $consumerSecret = "TKKBfdlU1I1GEqB9P3AZlybdC8YxW5qFSbuShkG7";
      $oauth1AccessToken = "qyprd5jgTqKPpZvNUM5OOLDEoPthaUnYRkDGP5o8Z4vmbUx5";
      $oauth1TokenSecret = "kFKirS5qfbj1j5naG2eRiHMROwsAS1AhW4aNweI1";
      $scopes = "com.intuit.quickbooks.accounting";
      $request = $oauth2Helper->createRequestToMigrateToken($consumerKey, $consumerSecret, $oauth1AccessToken, $oauth1TokenSecret, $scopes);
      $this->enableDebugForCurl($request, true);
  }

  public function testDoNotVerifySSL() : void{
    $oauth2Helper = $this->createClient();
    $code = "L011557358660z3axu8cgM7YHVyRGAaU63Ap0hgtEzfdkgwu5d";
    $request = $oauth2Helper->createRequestToExchange($code);
    $this->enableDebugForCurl($request, false);
  }
}
