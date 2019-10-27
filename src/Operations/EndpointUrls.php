<?php
namespace QuickBooksOnline\Payments\Operations;

class EndpointUrls
{
    const CHARGE_URL = "/quickbooks/v4/payments/charges";
    const CUSTOMER_URL = "/quickbooks/v4/customers";
    const TOKEN_URL = "/quickbooks/v4/payments/tokens";
    const TOKEN_URL_IE = "/quickbooks/v4/payments/tokens/ie";
    const ECHECK_URL = "/quickbooks/v4/payments/echecks";
    const VOID_URL = '/quickbooks/v4/payments/txn-requests';
}
