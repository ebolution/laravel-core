<?php

namespace Ebolution\Core\Domain\Exceptions;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use ReflectionClass;

class CustomException extends Exception
{
    #[ArrayShape(['status' => "int|mixed", 'error' => "bool", 'class' => "false|string", 'message' => "string"])]
    public function toException(): array
    {
        $classTemporally = new ReflectionClass(get_class($this));
        $class = explode('\\', $classTemporally->getName());

        return [
            'status'    => $this->getCode(),
            'error'     => true,
            'class'     => end($class),
            'message'   => $this->getMessage()
        ];
    }
}
