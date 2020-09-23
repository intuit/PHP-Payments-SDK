<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
//require "vendor/autoload.php";
include('../src/config.php');
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
      "expMonth" => "12",
      "expYear" => "2021",
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
