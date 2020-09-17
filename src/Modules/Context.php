<?php
namespace QuickBooksOnline\Payments\Modules;

use QuickBooksOnline\Payments\Operations\OperationsConverter;

class Context
{
    public $deviceInfo;
    public $mobile;
    public $recurring;
    public $isEcommerce;
    public $tax;
    public $reconBatchID;
    public $paymentGroupingCode;
    public $txnAuthorizationStamp;
    public $paymentStatus;
    public $merchantAccountNumber;
    public $clientTransID;

    public function __construct(array $array = array())
    {
        foreach ($array as $name => $value) {
            if (property_exists(get_class($this), $name)) {
                if (isset($value)) {
                    if (is_array($value)) {
                        $className = ModulesConstants::NAMESPACE_Modules . OperationsConverter::toUpperCaseClassName($name);
                        $obj = new $className($value);
                        $this->{$name} = $obj;
                    } else {
                        $this->{$name} = $value;
                    }
                }
            }
        }
    }
}
