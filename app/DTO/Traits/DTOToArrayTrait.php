<?php

namespace App\DTO\Traits;

trait DTOToArrayTrait
{
    public function toArray(): array
    {
        $classMethods = get_class_methods(__CLASS__);
        $getMethods = array_filter($classMethods, fn($methodName) => substr($methodName, 0, 3) === 'get');

        $array = [];

        foreach ($getMethods as $getMethodName) {
            $propertyName = lcfirst(substr($getMethodName, 3));
            $value = $this->$getMethodName();
            $array[$propertyName] = $value;
        }

        return $array;
    }
}
