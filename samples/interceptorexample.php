<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
//require "vendor/autoload.php";
include('../src/config.php');

use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Operations\ChargeOperations;
use QuickBooksOnline\Payments\Interceptors\{ConsoleLoggerInterceptor, LoggingInterceptor, ExceptionOnErrorInterceptor};



$client = new PaymentClient([
  'access_token' => "The accessToken",
  'environment' => "sandbox" //  or 'environment' => "production"
]);

$client->addInterceptor("FileInterceptor", new LoggingInterceptor("/Users/hlu2/Desktop/newFolderForLog/logTest/", 'America/Los_Angeles'));
$client->addInterceptor("LoggerInterceptor", new ConsoleLoggerInterceptor("/Users/hlu2/Desktop/newFolderForLog/logTest/errorLog.txt"));

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
$card = $this->createCardBody();
$clientId = rand();
$response = $client->createCard($card, $clientId, rand() . "abd");

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
