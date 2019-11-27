
QuickBooks Payments PHP SDK
==========================
![Build status](https://travis-ci.com/intuit/PHP-Payments-SDK.svg?branch=master)
![Latest Stable Version](https://poser.pugx.org/quickbooks/payments-sdk/v/stable)

This SDK is designed to facilitate developers using the QuickBooks Payments API by providing a set of methods that make it easier to work with.  It supports the following operations:

 - Standard OAuth 2.0 and OpenID Connect protocols
 - Interceptors for logging and error handling
 - Standard Payments API endpoints with requests/response handling

If you have not used the QuickBooks Payments API before, please go to our docs at: https://developer.intuit.com/app/developer/qbpayments/docs/get-started

## Requirements

   1. PHP 7.2.0 and later.
   2. App for QuickBooks Online Payments API.


## Composer

You can install the package via [Composer](http://getcomposer.org/). Run the following command:

```php
composer require quickbooks/payments-sdk
```

If you encounter a problem during composer installation, try to update composer first:

```php
composer update
```
and see if specifying a version resolves the issue, example:

```php
composer require quickbooks/payments-sdk 1.0.5
```

To use the package, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require "vendor/autoload.php";
```

## Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/intuit/PHP-Payments-SDK/releases). Then, to use the package, include the `config.php` file.

```php
require_once('/path/to/config.php');
```

## Dependencies

The following extensions are required in order to work properly:

- [`curl`](https://secure.php.net/manual/en/book.curl.php), although you can use your own non-cURL client if you prefer
- [`json`](https://secure.php.net/manual/en/book.json.php)

If you specify the Guzzle client, then Guzzle is also required.

If you use Composer, these dependencies should be handled automatically. If you install manually, you'll want to make sure that these extensions are available.

## Getting Started

To begin using the Payments SDK, the first step is to create the PaymentClient.  The minimum required properties are `access_token` and `environment`. The `access_token` is the OAuth 2.0 token to access the API, the `environment` is defining the base url for the API endpoint. This can be either `sandbox` or `production`. If you do not provide these two properties, the `PaymentClient` will still be created, however, it will not be able to make any Payments API calls.

```php
$client = new PaymentClient([
 'access_token' => "your access token",
 'environment' => "sandbox" //  or 'environment' => "production"
]);
```
Once the Payment Client is created, you can start making API calls to the Payments API endpoints. Simple usage looks like:

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

A call could fail for reasons such as an expired token, server outrage, or invalid request body.  You can check this by using `$response->failed()`. Check with our [Response Interface](https://github.com/intuit/PHP-Payments-SDK/blob/master/src/HttpClients/Response/ResponseInterface.php) for a list of supported operations to diagnose a failed request. 

## OAuth support

Developers can use this library to handle the OAuth 2.0 protocol. It supports:
- Generating the Authorization URL             
`generateAuthCodeURL(string $scope, string $userDefinedState = "state") : string`
- Getting the OAuth 2.0 Bearer Token              
`createRequestToExchange(string $code) : RequestInterface`  
- Getting User Info                        
`createRequestForUserInfo(string $accessToken) : RequestInterface`
- Refreshing the OAuth 2.0 Token                 
`createRequestToRefresh(string $refreshToken) : RequestInterface`
- Revoking the OAuth 2.0 Token                    
`createRequestToRevoke(string $token)`
- Migrating tokens from OAuth 1.0 to OAuth 2.0 
`createRequestToMigrateToken(string $consumerKey, string $consumerSecret, string $oauth1AccessToken, string $oauth1TokenSecret, string $scopes) : RequestInterface`

In order to use OAuth 2.0, developers will need to create two objects
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

The PHP Payments SDK supports all six Payments endpoints: `BankAccounts`, `Cards`, `Charges`, `EChecks`, `Tokens`, `Transactions`.  These can be found in our [QuickBooks Payments API Explorer](https://developer.intuit.com/app/developer/qbpayments/docs/api/resources/all-entities/bankaccounts).

To construct the body for each API endpoint, developers will need to first construct the body of the request in array format, then use the `Operations`'s `buildFrom` method to convert the array to an object.

For example, to convert an array representation of a card to a $card object, use:
```php
CardOperations::buildFrom($cardarray);
```

All supported `operations` are availble here: [Operations](https://github.com/intuit/PHP-Payments-SDK/tree/master/src/Operations)

Once the object is created, you can use it to send the request. The function names of all the endpoints are derived from the [API reference](https://github.com/intuit/PHP-Payments-SDK/tree/master/src/Operations). For example, to create a card, you should use:
```php
$client->createCard($card);
```

If you want to provide a custom Request-Id for the request, use:
```php
$client->createCard($card, $requestId);
```
otherwise, a system generated random request ID will be provided.

To check a list of available functions, refer here: [API endpoints operations](https://github.com/intuit/PHP-Payments-SDK/blob/master/src/PaymentClient.php)


## Interceptors

Interceptors are used to intercept requests and responses. It can be used to log a request or response. This can be used as a retry mechanism for issues such as connection timeouts or token expirations.

The default interceptors provided in this SDK are:
 - **StackTraceLoggerInterceptor**: 
     Similar to log4j, it will log the stack trace to a log file. The StackTraceLoggerIntercepto will log everything to one error log.
 - **RequestResponseLoggerInterceptor**: 
     It will log the complete request and response that was sent and received each time. It will hide the OAuth 2.0 token. The RequestResponseLoggerInterceptor will record each request in a file, and each response in a file.
 - **ExceptionOnErrorInterceptor**: 
     It will throw an exception if the request failed. 
     
  
Based on the need, you can define your own interceptors to intercept requests and responses by inheriting the  `InterceptorInterface`:
- To change the request sent to the Payments API, define your `before(RequestInterface &$request, PaymentClient $client)` method.
- To change the response received, define your `after(ResponseInterface &$response, PaymentClient $client)` method. 
- To intercept the unaltered request and response, define your `intercept(RequestInterface $request, ResponseInterface $response, PaymentClient $client)` method.

In order to add an interceptor to the client, use:

```php
$client->addInterceptor("interceptorName", new InterceptorImplementation());
```

To delete an interceptor by its name, use:

```php
$client->removeInterceptor($name);
```

Example:
To enable file storage for each acutal request and response sent by the SDK:
```php
$client->addInterceptor("requestresponselogger", new RequestResponseLoggerInterceptor("/your/directory/to/store/files", 'America/Los_Angeles'));
```

To enable logging of each transaction sent by the SDK:
```php
$client->addInterceptor("tracelogger", new StackTraceLoggerInterceptor("/your/file/to/log/the/transaction", 'America/Los_Angeles'));
```


## Error Handling

By default, each `$response` object has a `failed()` method to determine if a request was made successfully. A successful request is defined by having a status code of 200/201. Any other status code will cause the `$response->failed()` method to return true. If a request failed, you can use our [supported diagnose function](https://github.com/intuit/PHP-Payments-SDK/blob/master/src/HttpClients/Response/ResponseInterface.php) to find out more information.

## Questions

If you find any bugs for the SDK, or want to request for a feature, create a support ticket at https://help.developer.intuit.com/s/ or send us a pull request. Contributions are always welcomed!

