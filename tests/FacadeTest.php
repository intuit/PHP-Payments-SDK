<?php
declare(strict_types=1);

namespace QuickBooksOnline\Tests;

use PHPUnit\Framework\TestCase;
use QuickBooksOnline\Payments\Operations\OperationsConverter;

final class FacadeTest extends TestCase
{
    public function testJsonDecode(): void
    {
        $this->expectException(\RuntimeException::class);
        OperationsConverter::objectFrom("something, is not json}", "Charge");
    }
}
