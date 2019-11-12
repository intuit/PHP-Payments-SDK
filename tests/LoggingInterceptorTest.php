<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\{CardOperations, ChargeOperations};
use QuickBooksOnline\Payments\PaymentClient;
use QuickBooksOnline\Payments\Interceptors\{StackTraceLoggerInterceptor, RequestResponseLoggerInterceptor, ExceptionOnErrorInterceptor};


final class LoggingInterceptorTest extends TestCase
{
    private $testDir = "/tmp/logTest";
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

    protected function setUp(): void
    {
        $this->testDir = $this->testDir . '_' . str_pad(strval(mt_rand(1,99999999)), 8, '0', STR_PAD_LEFT);
        if (!file_exists($this->testDir))
        {
            mkdir($this->testDir, 0755, TRUE);
        }
    }
    protected function tearDown(): void
    {
        if (file_exists($this->testDir)) {
            foreach (glob($this->testDir . '/*') as $file) {
                if (!is_dir($file)) {
		    unlink($file);
		}
            }
            rmdir($this->testDir);
        }
    }

    public function testLoggingToDiskWorksOrNot() : void
    {
        $client = $this->createInstance();
        $client->addInterceptor("FileInterceptor", new RequestResponseLoggerInterceptor($this->testDir . '/', 'America/Los_Angeles'));
        $client->addInterceptor("LoggerInterceptor", new StackTraceLoggerInterceptor($this->testDir . '/errorLog.txt'));
        $card = $this->createCardBody();
        $clientId = rand();
        $response = $client->createCard($card, $clientId, rand() . "abd");
        $exist = file_exists($this->testDir . '/errorLog.txt');
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
