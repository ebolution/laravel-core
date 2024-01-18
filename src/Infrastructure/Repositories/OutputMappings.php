<?php

namespace Ebolution\Core\Infrastructure\Repositories;

trait OutputMappings
{
    protected function setMappers(string $configKey): void
    {
        global $__mappers__;

        $__mappers__ = config($configKey, []);
    }

    protected function mapElement(string $type, array $element): array
    {
        global $__mappers__;

        $mapper_class = $__mappers__[$type] ?? null;
        if ( $mapper_class ) {
            $mapper = app()->make($mapper_class);
            $result = $mapper($element);

            return $result;
        }

        return $element;
    }

    protected function mapList(string $type, array $elements): array
    {
        global $__mappers__;

        $mapper_class = $__mappers__[$type] ?? null;
        if ( $mapper_class ) {
            $mapper = app()->make($mapper_class);
            $result = [];
            foreach ($elements as $element) {
                $result[] = $mapper($element);
            }

            return $result;
        }

        return $elements;
    }
}
