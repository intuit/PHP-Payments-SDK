<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\{CardOperations, ChargeOperations};
use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Interceptors\{StackTraceLoggerInterceptor, RequestResponseLoggerInterceptor, ExceptionOnErrorInterceptor};


final class LoggingInterceptorTest extends TestCase
{
    private function createInstance()
    {
        return TestClientCreator::createInstance();
    }
    private function createCardBody()
    {
        $cardBody = CardOperations::buildFrom([
        "expMonth"=> "12",
            "address"=> [
              "postalCode"=> "44112",
              "city"=> "Richmond",
              "streetAddress"=> "1245 Hana Rd",
              "region"=> "VA",
              "country"=> "US"
            ],
            "number"=> "4131979708684369",
            "name"=> "Test User",
            "expYear"=> "2026"
      ]);
        return $cardBody;
    }

    public function testLoggingToDiskWorksOrNot() : void
    {
        $client = $this->createInstance();
        $client->addInterceptor("FileInterceptor", new RequestResponseLoggerInterceptor("/Users/hlu2/Desktop/newFolderForLog/logTest/", 'America/Los_Angeles'));
        $client->addInterceptor("LoggerInterceptor", new StackTraceLoggerInterceptor("/Users/hlu2/Desktop/newFolderForLog/logTest/errorLog.txt"));
        $card = $this->createCardBody();
        $clientId = rand();
        $response = $client->createCard($card, $clientId, rand() . "abd");
        $exist = file_exists("/Users/hlu2/Desktop/newFolderForLog/logTest/errorLog.txt");
        $this->assertEquals(
            $exist, true
        );

    }

    // public function testCanChangeRequestAndResponse() : void
    // {
    //   $chargeBody = ChargeOperations::buildFrom([
    //       "amount" => "10.55",
    //       "currency" => "USD",
    //       "capture" => false,
    //       "card" => [
    //           "name" => "emulate=0",
    //           "number" => "4111111111111111",
    //           "address" => [
    //             "streetAddress" => "1130 Kifer Rd",
    //             "city" => "Sunnyvale",
    //             "region" => "CA",
    //             "country" => "US",
    //             "postalCode" => "94086"
    //           ],
    //           "expMonth" => "02",
    //           "expYear" => "2020",
    //           "cvc" => "123"
    //       ],
    //       "context" => [
    //         "mobile" => "false",
    //         "isEcommerce" => "true"
    //       ]
    //     ]);
    //
    //     $client = $this->createInstance();
    //     $request = ChargeOperations::createChargeRequest($chargeBody, "sfas" . rand(), $client->getContext());
    //     $exceptionOnErrorInterceptor = new ExceptionOnErrorInterceptor();
    //     $client->send($request, $exceptionOnErrorInterceptor);
    //     $this->assertEquals(
    //         $exist, true
    //     );
    // }
}
