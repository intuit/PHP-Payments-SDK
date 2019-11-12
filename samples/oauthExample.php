<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
//require "vendor/autoload.php";
include('../config.php');
use QuickBooksOnline\Payments\OAuth\OAuth2Authenticator;
use QuickBooksOnline\Payments\PaymentClient;

$client = new PaymentClient();
$oauth2Helper = OAuth2Authenticator::create([
  'client_id' => 'ClientID',
  'client_secret' => 'ClientSecret',
  'redirect_uri' => 'https://developer.intuit.com/v2/OAuth2Playground/RedirectUrl',
  'environment' => 'development'
]);

$scope = "com.intuit.quickbooks.accounting openid profile email phone address";

$authorizationCodeURL = $oauth2Helper->generateAuthCodeURL($scope);
//https://appcenter.intuit.com/connect/oauth2?client_id=L0vmMZIfwUBfv9PPM96dzMTYATnLs6TSAe5SyVkt1Z4MAsvlCU&scope=com.intuit.quickbooks.accounting%20openid%20profile%20email%20phone%20address&redirect_uri=https%3A%2F%2Fdeveloper.intuit.com%2Fv2%2FOAuth2Playground%2FRedirectUrl&response_type=code&state=JBAJE

//Redirect User to the $authorizationCodeURL, and a code will be sent to your redirect_uri as query paramter;
$code = "SomeVeryUniqueString";
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
