<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\CardOperations;
use QuickBooksOnline\Payments\PaymentClient;

final class CardTest extends TestCase
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

    private function createCardBody2()
    {
        $cardBody = CardOperations::buildFrom([
        "expMonth"=> "11",
            "address"=> [
              "postalCode"=> "44112",
              "city"=> "Richmond",
              "streetAddress"=> "White Street 132",
              "region"=> "VA",
              "country"=> "US"
            ],
            "number"=> "4948759199127257",
            "name"=> "Sophia Perez",
            "expYear"=> "2022"
      ]);
        return $cardBody;
    }


    public function testCreateCardRequestOnSandbox(): void
    {
        $client = $this->createInstance();
        $card = $this->createCardBody();
        $clientId = rand();
        $response = $client->createCard($card, $clientId, rand() . "abd");
        $cardResponse = $response->getBody();
        $this->assertEquals(
            $cardResponse->name,
            $card->name
          );

        $this->assertEquals(
            $cardResponse->expYear,
            $card->expYear
          );
    }

    public function testDeleteCardRequestOnSandbox(): void
    {
        $client = $this->createInstance();
        $card = $this->createCardBody();
        $customerId = rand();
        $response = $client->createCard($card, $customerId, rand() . "abd");
        $cardResponse = $response->getBody();
        $response = $client->deleteCard($customerId, $cardResponse->id, rand() . "abd");
        $this->assertEquals(
            $response->getStatusCode(),
            "204"
          );

        $this->assertEmpty($response->getBody());
    }

    public function testallCardsOnSandbox(): void
    {
        $client = $this->createInstance();
        $card = $this->createCardBody();
        $card2 = $this->createCardBody2();
        $customerId = rand();
        $response = $client->createCard($card, $customerId);
        $id1 = $response->getBody()->id;
        $response = $client->createCard($card2, $customerId);
        $id2 = $response->getBody()->id;

        $response = $client->getAllCardsFor($customerId);
        $body = $response->getBody();
        $card1 = $body[0];
        $card2 = $body[1];

        $this->assertEquals(
                $card1->id,
                $id2
        );

        $this->assertEquals(
                  $card2->id,
                  $id1
        );

        $client->deleteCard($customerId, $id1);
        $client->deleteCard($customerId, $id2);
    }

    public function testFindACustomerCardOnSandbox(): void
    {
        $client = $this->createInstance();
        $card = $this->createCardBody();
        $customerId = rand();

        /** Add a test card */
        $response = $client->createCard($card, $customerId);
        $id1 = $response->getBody()->id;
        $secureCardNumber1 = $response->getBody()->number;

        /** Retrieve the test card */
        $response2 = $client->getCard($customerId, $id1);
        $id2 = $response2->getBody()->id;
        $secureCardNumber2 = $response->getBody()->number;

        /** Make sure the retrieved secure card matches the originally added card */
        $this->assertEquals($id1, $id2);
        $this->assertEquals($secureCardNumber1, $secureCardNumber2);

        $client->deleteCard($customerId, $id1);
    }

    public function testCreateCardToken(): void
    {
        $client = $this->createInstance();
        $card = $this->createCardBody();
        $response = $client->createToken($card);
        $value = $response->getBody()->value;
        $customerId = rand();
        $response = $client->createCardFromToken($customerId, $value);
        $this->assertEquals(
                    $card->expMonth,
                    $response->getBody()->expMonth
            );

            $this->assertEquals(
              $card->name,
              $response->getBody()->name
            );
    }
}
