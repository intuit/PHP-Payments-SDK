<?php
namespace QuickBooksOnline\Payments\Modules;

use QuickBooksOnline\Payments\Operations\OperationsConverter;

class BankAccount extends Entity
{
    public $updated;
    public $name;
    public $routingNumber;
    public $accountNumber;
    public $inputType;
    public $accountType;
    public $phone;
    public $country;
    public $bankName;
    public $bankCode;
    public $default;
    public $entityVersion;
    public $entityId;
    public $entityType;

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
