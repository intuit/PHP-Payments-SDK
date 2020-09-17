<?php
namespace QuickBooksOnline\Payments\Modules;

use QuickBooksOnline\Payments\Operations\OperationsConverter;

class CardPresent
{
    public $track1;
    public $track2;
    public $ksn;
    public $pinBlock;

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
