<?php
namespace QuickBooksOnline\Payments\Modules;

use QuickBooksOnline\Payments\Operations\OperationsConverter;

class Card extends Entity
{
    public $updated;
    public $name;
    public $number;
    public $address;
    public $commercialCardCode;
    public $cvcVerification;
    public $cardType;
    public $expMonth;
    public $expYear;
    public $default;
    public $isBusiness;
    public $isLevel3Eligible;
    public $cvc;
    public $entityVersion;
    public $entityId;
    public $entityType;
    public $zeroDollarVerification;

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
