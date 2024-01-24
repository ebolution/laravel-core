<?php

namespace Ebolution\Core\Infrastructure\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Apply to an array of data the same casts that Eloquent will
 * perform over model properties.
 */
trait CastHelper
{
    public function cast(Model $model, array $data): array
    {
        $result = [];
        $casts = $model->getCasts();
        foreach($data as $key => $value) {
            $casted_value = $value;
            if ( array_key_exists($key, $casts) ) {
                $casted_value = $this->cast_element($casts[$key], $value);
            }
            $result[$key] = $casted_value;
        }

        return $result;
    }

    private function cast_element(string $type, string $value): int|string|null|Carbon|float
    {
        return match ($type) {
            'int' => intval($value),
            'hashed' => Hash::make($value),
            'datetime' => Carbon::make($value),
            'float'     => floatval($value),
            default => $value,
        };
    }
}
