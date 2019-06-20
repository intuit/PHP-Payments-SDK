<?php
namespace QuickBooksOnline\Payments\HttpClients\Request;

final class RequestFactory
{
    public static function createStandardIntuitRequest($type)
    {
        return new IntuitRequest($type);
    }
}
