<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\ECheckOperations;
use QuickBooksOnline\Payments\PaymentClient;

final class ECheckTest extends TestCase
{
  private function createInstance()
  {
    return TestClientCreator::createInstance();
  }

    private function createECheckBody()
    {
        $echeckBody = ECheckOperations::buildFrom([
          "bankAccount"=> [
       "phone"=> "1234567890",
       "routingNumber"=> "490000018",
       "name"=> "Fname LName",
       "accountType"=> "PERSONAL_CHECKING",
       "accountNumber"=> "1100000033345678"
     ],
     "description"=> "Check Auth test call",
     "checkNumber"=> str_pad(strval(mt_rand(1,99999999)), 8, '0', STR_PAD_LEFT),
     "paymentMode"=> "WEB",
     "amount"=> "5.55",
     "context"=> [
       "deviceInfo"=> [
         "macAddress"=> "macaddress",
         "ipAddress"=> "34",
         "longitude"=> "longitude",
         "phoneNumber"=> "phonenu",
         "latitude"=> "",
         "type"=> "type",
         "id"=> "1"
       ]
     ]
    ]);
        return $echeckBody;
    }

    public function testRetrieveECheck() : void
    {
        $client = $this->createInstance();
        $echeckBody = $this->createECheckBody();
        $response = $client->debit($echeckBody);
        $id = $response->getBody()->id;
        $response = $client->retrieveECheck($id);
        $this->assertEquals(
            $response->getBody()->id,
            $id
        );
    }

    public function testCreateDebit() : void
    {
        $client = $this->createInstance();
        $echeckBody = $this->createECheckBody();
        $response = $client->debit($echeckBody);
        $this->assertEquals(
            $response->getBody()->checkNumber,
            $echeckBody->checkNumber
        );
        $this->assertEquals(
            $response->getBody()->amount,
            $echeckBody->amount
        );
    }

    public function testVoidorRefundEchecks() : void
    {
        $client = $this->createInstance();
        $echeckBody = $this->createECheckBody();
        $response = $client->debit($echeckBody);
        $id = $response->getBody()->id;
        $body = ECheckOperations::buildFrom([
           "amount" => 5.55
        ]);
        $response = $client->voidOrRefundEcheck($body, $id);
        $this->assertEquals(
            $response->getBody()->amount,
            $echeckBody->amount
        );
    }


}
