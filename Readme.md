
QuickBooks Payments PHP SDK
==========================
This SDK is designed to facilitate developers using QuickBooks Online Payments API. It provides a set of methods that make it easier to work with Intuit’s Payments API. It supports the following operations:

 - Standard OAuth 2.0 and OpenID Connect protocols
 - Interceptors for logging and error handling
 - Standard Payments API endpoints requests/response handling

If you have not used with QuickBooks Online Payments API, please go to our docs at: https://developer.intuit.com/app/developer/qbpayments/docs/get-started

## Requirements

   1. PHP 7.0.0 and later.
   2. App for QuickBooks Online Payments API.


## Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```php
composer require quickbooks/payments-sdk
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require "vendor/autoload.php";
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/intuit/PHP-Payments-SDK/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/init.php');
```

## Dependencies

The bindings require the following extensions in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
- [`json`](https://secure.php.net/manual/en/book.json.php)

If you specify the Guzzle client, then Guzzle is also required.

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Getting Started

Simple usage looks like:

```php
<?php
require "vendor/autoload.php";

use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Operations\ChargeOperations;


$client = new PaymentClient([
  'access_token' => "your access token",
  'environment' => "sandbox" //  or 'environment' => "production"
]);

$array = [
  "amount" => "10.55",
  "currency" => "USD",
  "card" => [
      "name" => "emulate=0",
      "number" => "4111111111111111",
      "address" => [
        "streetAddress" => "1130 Kifer Rd",
        "city" => "Sunnyvale",
        "region" => "CA",
        "country" => "US",
        "postalCode" => "94086"
      ],
      "expMonth" => "02",
      "expYear" => "2020",
      "cvc" => "123"
  ],
  "context" => [
    "mobile" => "false",
    "isEcommerce" => "true"
  ]
];
$charge = ChargeOperations::buildFrom($array);
$response = $client->charge($charge);

if($response->failed()){
    $code = $response->getStatusCode();
    $errorMessage = $response->getBody();
    echo "code is $code \n";
    echo "body is $errorMessage \n";
}else{
    $responseCharge = $response->getBody();
    //Get the Id of the charge request
    $id = $responseCharge->id;
    //Get the Status of the charge request
    $status = $responseCharge->status;
    echo "Id is " . $id . "\n";
    echo "status is " . $status . "\n";
}

```

If the request is made successfully, then the `getBody()` function will return the casted object. The above example will return the following `$responseCharge` object from `$response->getBody()`:
```php
class QuickBooksOnline\Payments\Modules\Charge#11 (19) {
  public $status =>
  string(8) "CAPTURED"
  public $amount =>
  string(5) "10.55"
  public $currency =>
  string(3) "USD"
  public $token =>
  NULL
  public $card =>
  class QuickBooksOnline\Payments\Modules\Card#12 (18) {
    public $updated =>
    NULL
    public $name =>
    string(9) "emulate=0"
    public $number =>
    string(16) "xxxxxxxxxxxx1111"
    public $address =>
    class QuickBooksOnline\Payments\Modules\Address#13 (5) {
      public $streetAddress =>
      string(13) "1130 Kifer Rd"
      public $city =>
      string(9) "Sunnyvale"
      public $region =>
      string(2) "CA"
      public $country =>
      string(2) "US"
      public $postalCode =>
      string(5) "94086"
    }
    public $commercialCardCode =>
    NULL
    ...
```

Each casted object will have the same property names as stated in our API reference: https://developer.intuit.com/app/developer/qbpayments/docs/api/resources/all-entities/charges page,

If the request is failed due to token expired, server outrage, or invalid request body, use `$response->failed()` method to check if the request failed. Check with our [Response Interface](https://github.com/intuit/PHP-Payments-SDK/blob/master/src/HttpClients/Response/ResponseInterface.php) for a list of supported operations to diagnose a failed request. 

## OAuth support

Developers can use this library to handle OAuth 2.0 protocol. It supports:
- Generating Authorization URL             
`generateAuthCodeURL(string $scope, string $userDefinedState = "state") : string`
- Getting OAuth2 Bearer Token              
`createRequestToExchange(string $code) : RequestInterface`  
- Getting User Info                        
`createRequestForUserInfo(string $accessToken) : RequestInterface`
- Refreshing OAuth2 Token                  
`createRequestToRefresh(string $refreshToken) : RequestInterface`
- Revoking OAuth2 Token                    
`createRequestToRevoke(string $token)`
- Migrating tokens from OAuth1.0 to OAuth2 
`createRequestToMigrateToken(string $consumerKey, string $consumerSecret, string $oauth1AccessToken, string $oauth1TokenSecret, string $scopes) : RequestInterface`

In order to use OAuth 2, developers will need to create two objects
 - OAuth2Authenticator : used to create OAuth 2.0 related request
 - PaymentClient: used to send OAuth 2.0 related request
 
The Payments SDK does not provide any parsing support for parsing the OAuth 2.0 response. Since the response for OAuth 2.0 requests are always in JSON format, a simple `json_decode($response->getBody(), true)` will work.

Example:
```php
<?php
require "vendor/autoload.php";

use QuickBooksOnline\Payments\OAuth\OAuth2Authenticator;
use QuickBooksOnline\Payments\PaymentClient;

$client = new PaymentClient();
$oauth2Helper = OAuth2Authenticator::create([
  'client_id' => 'Your Client ID',
  'client_secret' => 'Your Client Secret',
  'redirect_uri' => 'The redirect URI under the app's Keys tab',
  'environment' => 'development' // or 'environment' => 'production' 
]);

//The scope for the token. 
$scope = "com.intuit.quickbooks.accounting openid profile email phone address";

$authorizationCodeURL = $oauth2Helper->generateAuthCodeURL($scope);
//Sample URL: https://appcenter.intuit.com/connect/oauth2?client_id=L0vmMZIfwUBfv9PPM96dzMTYATnLs6TSAe5SyVkt1Z4MAsvlCU&scope=com.intuit.quickbooks.accounting%20openid%20profile%20email%20phone%20address&redirect_uri=https%3A%2F%2Fdeveloper.intuit.com%2Fv2%2FOAuth2Playground%2FRedirectUrl&response_type=code&state=JBAJE

//Redirect User to the $authorizationCodeURL, and a code will be sent to your redirect_uri as query paramter
$code = $_GET['code'];
$request = $oauth2Helper->createRequestToExchange($code);
$response = $client->send($request);
if($response->failed()){
  $code = $response->getStatusCode();
  $errorMessage = $response->getBody();
  echo "code is $code \n";
  echo "body is $errorMessage \n";
}else{
  //Get the keys
  $array = json_decode($response->getBody(), true);
  $refreshToken = $array["refresh_token"];
  //AB11570127472xkApQcZmbTMGfzzEOgMWl2Br5h8IEgxRULUbO
}

```

## Operations

The PHP Payments SDK supports all six Payments endpoints: `BankAccounts`, `Cards`, `Charges`, `EChecks`, `Tokens`, `Transactions` that mentioned in the docs: [API Reference](https://developer.intuit.com/app/developer/qbpayments/docs/api/resources/all-entities/bankaccounts#create-a-bank-account-from-a-token).

To construct the body for each API endpoint, developers will need to first construct the body of the request in array format, then use the `Operations`'s `buildFrom` method to convert the array to object.

For example, to convert an array representation of card to a $card object, use:
`CardOperations::buildFrom($cardarray);`

All supports `operations` are availble here: [Operations](https://github.com/intuit/PHP-Payments-SDK/tree/master/src/Operations)

Once the object is created, developers will use the `$client` corresponding functions call to send the request. The function names of all the endpoints are derived from the doc. For example To create a card, developers will use
```php
$client->createCard($card);
```

To check a list of available functions, refer to here: [API endpoints operations](https://github.com/intuit/PHP-Payments-SDK/blob/master/src/PaymentClient.php)


## Interceptors

Interceptors are used to intercept requests and response.

Developers can define their own interceptors to intercept request and response by inheriting `InterceptorInterface`.

To change the request send, define your `before(RequestInterface &$request, PaymentClient $client)` method.
To change the response received, define your `after(ResponseInterface &$response, PaymentClient $client)` method. 
To intercept the request and response, but don't alter them, define your `intercept(RequestInterface $request, ResponseInterface $response, PaymentClient $client)` method.

THe default interceptors provided in this SDK are:
 - ConsoleLoggerInterceptor
 - LoggingInterceptor
 - ExceptionOnErrorInterceptor

In order to add interceptor to the client, use:

```php
$client->addInterceptor("interceptorName", new InterceptorImplementation());
```

To delete an interceptor by name, use:

```php
$client->removeInterceptor($name);
```

Example:

To enable file storage for each acutal request and response sent by the SDK:
```php
$client->addInterceptor("requestresponselogger", new LoggingInterceptor("/your/directory/to/store/files", 'America/Los_Angeles'));
```

To enable logging each transaction sent by the SDK:
```php
$client->addInterceptor("tracelogger", new ConsoleLoggerInterceptor("/your/file/to/log/the/transaction", 'America/Los_Angeles'));
```


### Calling API endpoints.

The SDK supports multiple endpoints:
- Charge
- Token
- Card
- EChecks
- BankAccounts

For each endpoint, please refer to our documentation:
https://developer.intuit.com/app/developer/qbpayments/docs/api/resources/all-entities/bankaccounts

Example for creating charge:
```php

$client = new PaymentClient([
  'access_token' : ""
  'refresh_token' : ""
]);

$array = [
  "amount" => "10.55",
  "currency" => "USD",
  "card" => [
      "name" => "emulate=0",
      "number" => "4111111111111111",
      "address" => [
        "streetAddress" => "1130 Kifer Rd",
        "city" => "Sunnyvale",
        "region" => "CA",
        "country" => "US",
        "postalCode" => "94086"
      ],
      "expMonth" => "02",
      "expYear" => "2020",
      "cvc" => "123"
  ],
  "context" => [
    "mobile" => "false",
    "isEcommerce" => "true"
  ]
];
$chargeBody = ChargeBuilder::buildFrom($array);
$chargeRequest = ChargeBuilder::createChargeRequest($chargeBody, $request_id);

$response = $client->send($chargeRequest);
```

### Accessing response data

You can access the data from the last API response on any object via `getLastResponse()`.

```php

```

### SSL / TLS compatibility issues

QuickBooks Online API now requires that [all connections use TLS 1.2](https://developer.intuit.com/app/developer/homepage). Some systems (most notably some older CentOS and RHEL versions) are capable of using TLS 1.2 but will use TLS 1.0 or 1.1 by default. In this case, you'd get an `invalid_request_error`/

The recommended course of action is to [upgrade your cURL and OpenSSL packages](https://support.stripe.com/questions/how-do-i-upgrade-my-stripe-integration-from-tls-1-0-to-tls-1-2#php) so that TLS 1.2 is used by default, but if that is not possible, you might be able to solve the issue by setting the `CURLOPT_SSLVERSION` option to either `CURL_SSLVERSION_TLSv1` or `CURL_SSLVERSION_TLSv1_2`:
