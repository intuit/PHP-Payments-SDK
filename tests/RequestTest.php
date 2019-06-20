<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

use QuickBooksOnline\Payments\HttpClients\Request\{RequestInterface, RequestFactory, IntuitRequest};

final class RequestTest extends TestCase
{

  public function testCanCreateRequestThroughFactoryMethod(): void
  {
      $intuitRequest = RequestFactory::createStandardIntuitRequest(RequestType::OAUTH);

      $this->assertInstanceOf(
          IntuitRequest::class,
          $intuitRequest
      );
  }

  public function testRequestMethod(): void
  {
      $intuitRequest = RequestFactory::createStandardIntuitRequest(RequestType::OAUTH);
      $intuitRequest->setMethod(RequestInterface::GET);
      $this->assertEquals(
          "GET",
          $intuitRequest->getMethod()
      );
      $intuitRequest->setMethod(RequestInterface::POST);
      $this->assertEquals(
          "POST",
          $intuitRequest->getMethod()
      );
  }


}
