<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\BankAccountOperations;
use QuickBooksOnline\Payments\PaymentClient;

final class BankAccountTest extends TestCase
{
  private function createInstance()
  {
    return TestClientCreator::createInstance();
  }

  private function createBankBody()
  {
      $cardBody = BankAccountOperations::buildFrom([
      "phone" => "6047296480",
      "routingNumber" => "322079353",
      "name"=> "My Checking",
      "accountType"=> "PERSONAL_CHECKING",
      "accountNumber" => "11000000333456781"
    ]);
      return $cardBody;
  }

  public function testCreateBankAccount(): void
  {
      $client = $this->createInstance();
      $bank = $this->createBankBody();
      $clientId = rand();
      $response = $client->createBankAccount($bank, $clientId);
      $responseBank = $response->getBody();
      $rountingNumber = substr($responseBank->routingNumber, -4);
      $passedNumber = substr($bank->routingNumber, -4);
      $this->assertEquals(
          $rountingNumber,
          $passedNumber
        );
  }

  public function testCreateTokenForBank(): void
  {
      $client = $this->createInstance();
      $bank = $this->createBankBody();
      $clientId = rand();
      $response = $client->createToken($bank);
      $token = $response->getBody();
      $this->assertNotNull(
        $token->value
        );
  }

  public function testCreateBankAccountFromToken(): void
  {
      $client = $this->createInstance();
      $bank = $this->createBankBody();
      $clientId = rand();
      $response = $client->createToken($bank);
      $token = $response->getBody()->value;
      $response = $client->createBankAccountFromToken(rand(), $token);

      $rountingNumber = substr($response->getBody()->routingNumber, -4);
      $passedNumber = substr($bank->routingNumber, -4);
      $this->assertEquals(
          $rountingNumber,
          $passedNumber
        );
  }

  public function testDeleteBankAccount(): void
  {
    $client = $this->createInstance();
    $bank = $this->createBankBody();
    $clientId = rand();
    $response = $client->createBankAccount($bank, $clientId);
    $responseBankId = $response->getBody()->id;
    $response = $client->deleteBankAccount($clientId, $responseBankId);
    $this->assertEquals(
        $response->getStatusCode(),
        204
      );
  }

  public function testgetAllBankaccounts(): void
  {
    $client = $this->createInstance();
    $bank = $this->createBankBody();
    $clientId = rand();
    $response = $client->createBankAccount($bank, $clientId);
    $responseBankId = $response->getBody()->id;
    $response = $client->getAllBankAccount($clientId);
    $bankAccount = $response->getBody()[0];

    $rountingNumber = substr($bankAccount->routingNumber, -4);
    $passedNumber = substr($bank->routingNumber, -4);
    $this->assertEquals(
        $rountingNumber,
        $passedNumber
      );
  }

  public function testgetBankAccounts(): void
  {
    $client = $this->createInstance();
    $bank = $this->createBankBody();
    $clientId = rand();
    $response = $client->createBankAccount($bank, $clientId);
    $responseBankId = $response->getBody()->id;
    $response = $client->getBankAccount($clientId, $responseBankId);

    $this->assertEquals(
        $responseBankId,
        $response->getBody()->id
      );
  }
}
