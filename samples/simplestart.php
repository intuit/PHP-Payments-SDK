<?php
//Replace the line with require "vendor/autoload.php" if you are using the Samples from outside of _Samples folder
//require "vendor/autoload.php";
include('../src/config.php');
use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Operations\ChargeOperations;


$client = new PaymentClient([
  'access_token' => "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..mDJrjB3DU7H-aY0UXgj-FQ._GHonQ_YitarEemB6gwuK6D6LJHjuuNBvl2Vcp8wn1DK_4KcmU2pAcp6dcljWY66_mDwAEsO-UDBQaPt2m4UP9uG12Gm2080Sskk-l7z4WWAbGtjOhgnyULB3FKR5ZEaZN9U0rEMtG7ux9grNIJr4EmMtTVYsQa1PdNSfHOXLBj9ixqXdE9zDkqgVioNows3JILlaqMqr3vz3yFhWQYimDmSFD1lwfb_TcB5P9iLNCTOFtvi0_gSM1_hkgC7H4rNsuyiCzt1KN5DpPQ3Dcc76t6NLT7JlLivMHHyjFq_QqWve-mnNrDK1nxSpE5wPoQLLKfg_0UuZIky6Ba_BGsr5PXxEozGTzLNsXpw8qGrUzn4gl9xe2m31DtULfDlhNHevzMbqHrO6uLwNyI2Nd0BnVpES0V7o2kpwg4P_ulj3jValmHtyv-yjsfA0fpmU0KFHpCYnBnhqKiybbsicHzsY6QpjotMrqek36-G3ZvkVSdANKLUu_PsgvStV2VnL_7ARbFYrDqqkAKhUKHoPLG0b40MXzKFGJrZi0OQfWVT3Xe_E8beRdkL6tGOrVcxXIsetn_qQ-TdDmXCteErO1iR17gV_4MqgByF07TP1qfmpothSQUC0HmAryX2fcdPDBmOcTrDYX7LquNdTBIoPuVuCLvZJshC2sVhKnz5Z1HBhqDkkyrwWqRZnRThUSunYUyyK4IlaNvC2JZg-OrtVbncJA.Fs_60T_tIyXcHEc_c6G0Kg",
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
