<?php

namespace Ebolution\Core\Infrastructure\Helpers;

trait StringHelper
{
    public function formatErrorsRequest(array $validator): string
    {
        $message = '';
        array_walk($validator, function ($value) use (&$message) {
           $message .= $value . '|';
        });

        return substr($message, 0, -1);
    }
}
