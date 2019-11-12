<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\OAuth\{DiscoverySandboxURLs, DiscoveryURLs, OAuth2Authenticator, OAuth1Encrypter};
use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface, IntuitRequest, RequestFactory};
use QuickBooksOnline\Payments\HttpClients\core\{HttpClientInterface, GuzzleClient};

final class GuzzleClientTest extends TestCase
{
  private function createClient() : OAuth2Authenticator {
    $oauth2Helper = OAuth2Authenticator::create([
      'client_id' => 'Q0K5t9wvMNSAMxsxfydrKY9RqBwIMCLF2wt8kOs9L4z6V69XuY',
      'client_secret' => 'DoMR0sxz4aRqpizlc1XD5hwVLcN1Ep8MtPuOIJFs',
      'redirect_uri' => 'https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl',
      'environment' => 'development'
    ]);
    return $oauth2Helper;
  }


  public function testExchangeCodeRequestSentByCurlClient(): void
  {
      $oauth2Helper = $this->createClient();
      $code = "AB11582326772xz6uxiWcSg4OxkVr6uzEiwibGVBKm32MuiEle";
      $request = $oauth2Helper->createRequestToExchange($code);
      $client = new GuzzleClient();
      $response = $client->send($request);
      $this->assertEquals(
          $response->getUrl(),
          $request->getUrl()
      );
  }

  public function testSuccessRefreshTokenSentByGuzzleClient(): void
  {
    $oauth2Helper = $this->createClient();
    $token = "AB11582326772xz6uxiWcSg4OxkVr6uzEiwibGVBKm32MuiEle";
    $request = $oauth2Helper->createRequestToRefresh($token);
    $client = new GuzzleClient();
    $response = $client->send($request);
    $array = json_decode($response->getBody(), true);
    $this->assertEquals(
        $token,
        $array["refresh_token"]
    );
  }


}
