<?php
namespace QuickBooksOnline\Payments\HttpClients\core;

/**
 * Constants whose values do not change.
 */
class CoreConstants
{
    public static function getCertPath()
    {
        return dirname(__FILE__) . "/certs/cacert.pem"; //Pem certification Key Path
    }

    const INTUIT_TID = "intuit_tid";
    const CONTENT_TYPE = "Content-Type";
}
