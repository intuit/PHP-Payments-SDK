<?php

namespace QuickBooksOnline\Payments\Operations;

use QuickBooksOnline\Payments\Modules\ModulesConstants;
use QuickBooksOnline\Payments\Modules\Token;
use QuickBooksOnline\Payments\HttpClients\Request\RequestType;

class OperationsConverter
{
    public static function toUpperCaseClassName(string $name)
    {
        return ucfirst($name);
    }

    public static function removeNullFrom($obj)
    {
        $obj = (object) array_filter((array)$obj, function ($val) {
            return $val !== null;
        });
        $properties = get_object_vars($obj);
        foreach ($properties as $key => $value) {
            if (is_object($value)) {
                $removed = OperationsConverter::removeNullFrom($value);
                $obj->{$key} = $removed;
            }
        }
        return $obj;
    }

    public static function getJsonFrom($obj)
    {
        $obj = OperationsConverter::removeNullFrom($obj);
        return json_encode($obj);
    }

    private static function isAssociatedArray($data) : bool
    {
        if (is_array($data)) {
            foreach ($data as $key => $dataMemeber) {
                if (is_integer($key)) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function objectFrom(string $body, string $type)
    {
        $arrayRepresent = json_decode($body, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \RuntimeException("Cannot convert $body to Object.");
        }
        $class = ModulesConstants::NAMESPACE_Modules . $type;
        if (class_exists($class)) {
            if (OperationsConverter::isAssociatedArray($arrayRepresent)) {
                $body = array();
                foreach ($arrayRepresent as $val) {
                    $obj = new $class($val);
                    $body[] = $obj;
                }
                return $body;
            } else {
                $obj = new $class($arrayRepresent);
                return $obj;
            }
        } else {
            throw new \RuntimeException("Class not found for " . $type);
        }
    }

    public static function updateResponseBodyToObj(&$response)
    {
        if (!$response->failed() && !empty($response->getBody())) {
            if (strcmp($response->getAssociatedRequest()->getRequestType(), RequestType::OAUTH) != 0) {
                $objBody = OperationsConverter::objectFrom($response->getBody(), $response->getAssociatedRequest()->getRequestType());
                $response->setBody($objBody);
            }
        }
    }

    public static function createTokenObjFromValue($val) : Token
    {
        $token = new Token();
        $token->value = $val;
        return $token;
    }
}
