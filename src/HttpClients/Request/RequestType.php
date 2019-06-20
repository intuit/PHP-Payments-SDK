<?php
namespace QuickBooksOnline\Payments\HttpClients\Request;

class RequestType
{
    const OAUTH = "OAuth";
    const USERINFO = "UserInfo";
    const CHARGE = "Charge";
    const CARD = "Card";
    const TOKEN = "Token";
    const ECHECK = "ECheck";
    const BANKACCOUNT = "BankAccount";
}
