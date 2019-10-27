<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\ChargeOperations;
use QuickBooksOnline\Payments\PaymentClient;

final class ChargeTest extends TestCase
{
    private function createInstance()
    {
        return TestClientCreator::createInstance();
    }

    private function createChargeBody()
    {
        $chargeBody = ChargeOperations::buildFrom([
            "amount" => "10.55",
            "currency" => "USD",
            "capture" => false,
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
                 ]);
        return $chargeBody;
    }

    private function createChargeBodyWithCapture()
    {
        $chargeBody = ChargeOperations::buildFrom([
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
                 ]);
        return $chargeBody;
    }


    private function createRefundBody()
    {
        $chargeBody = ChargeOperations::buildFrom([
            "amount" => "10.55",
            "description" => "first refund",
            "id" => "E5753FS0CL2F"
        ]);
        return $chargeBody;
    }

    private function createCaptureBody()
    {
        $chargeBody = ChargeOperations::buildFrom([
            "amount" => "10.55",
            "context" => [
                "mobile" => "false",
                "isEcommerce" => "true"
            ]
        ]);
        return $chargeBody;
    }

    public function testRequestId() : void
    {
        $client = $this->createInstance();
        $chargeBody = $this->createChargeBody();
        $requestId = rand() . "abd";
        $response = $client->charge($chargeBody, $requestId);
        //var_dump($response);
        $this->assertEquals(
            $response->getAssociatedRequest()->getHeader()['Request-Id'],
            $requestId
        );

        $response = $client->charge($chargeBody);
        $this->assertEquals(
              strlen($response->getAssociatedRequest()->getHeader()['Request-Id']),
              20
          );
    }

    public function testCreateChargeRequestOnSandbox(): void
    {
        $client = $this->createInstance();

        //No space in RequestId
        $chargeBody = $this->createChargeBody();
        $response = $client->charge($chargeBody, rand() . "abd");
        $chargeResponse = $response->getBody();
        $this->assertEquals(
            $chargeResponse->amount,
            $chargeBody->amount
        );

        $this->assertEquals(
            $chargeResponse->card->address->streetAddress,
            $chargeBody->card->address->streetAddress
        );
    }

    public function testGetCharge() :void
    {
        $client = $this->createInstance();

        $chargeBody = $this->createChargeBody();
        $response = $client->charge($chargeBody, rand() . "abd");
        $chargeResponse = $response->getBody();
        $id = $chargeResponse->id;

        $client->getHttpClient()->enableDebug();
        $response = $client->retrieveCharge($id, rand() . "abd");
        $information = $client->getHttpClient()->getDebugInfo();
        $this->assertEquals(
            $chargeResponse->id,
            $id
        );
    }

    public function testRefundCharge() :void
    {
        $client = $this->createInstance();

        $chargeBody = $this->createChargeBody();
        $response = $client->charge($chargeBody, rand() . "abd");
        $chargeResponse = $response->getBody();
        $id = $chargeResponse->id;
        $response = $client->refundCharge($this->createRefundBody(), $id, rand() . "abd");
        $refundResponse = $response->getBody();

        $this->assertEquals(
            $refundResponse->status,
            "ISSUED"
        );

        $this->assertEquals(
            $refundResponse->amount,
            $chargeBody->amount
        );
    }

    public function testCaptureCharge() : void
    {
        $client = $this->createInstance();
        $chargeBody = $this->createChargeBody();
        $response = $client->charge($chargeBody, rand() . "abd");
        $chargeResponse = $response->getBody();
        $id = $chargeResponse->id;
        $response = $client->captureCharge($this->createCaptureBody(), $id, rand() . "abd");
        $refundResponse = $response->getBody();
        $this->assertEquals(
            $refundResponse->status,
            "CAPTURED"
         );
    }

    public function testRefundById() : void
    {
        $client = $this->createInstance();

        $chargeBody = $this->createChargeBodyWithCapture();
        $response = $client->charge($chargeBody, rand() . "abd");
        $chargeResponse = $response->getBody();
        $id = $chargeResponse->id;
        $response = $client->refundCharge($this->createRefundBody(), $id, rand() . "abd");
        $refundResponse = $response->getBody();
        $chargeId = $refundResponse->id;
        $response = $client->getRefundDetail($id, $chargeId, rand() . "abd");
        $refundResponse = $response->getBody();
        $this->assertEquals(
            $refundResponse->id,
            $chargeId
         );
    }

    public function testVoidTransaction()
    {
        $client = $this->createInstance();
        $chargeBody = $this->createChargeBody();
        $chargeRequestId = rand() . "abd";

        $client->charge($chargeBody, $chargeRequestId);

        $voidResponse = $client->voidChargeTransaction($chargeRequestId);
        $voidBodyResponse = $voidResponse->getBody();

        $this->assertEquals($voidBodyResponse->status, 'ISSUED');
        $this->assertEquals($voidBodyResponse->type, "VOID");
    }
}
