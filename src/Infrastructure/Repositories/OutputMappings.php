<?php

namespace Ebolution\Core\Infrastructure\Repositories;

trait OutputMappings
{
    protected array $mappers = [];

    protected function setMappers(string $configKey): void
    {
        $this->mappers = config($configKey, []);
    }

    protected function mapElement(string $type, array $element): array
    {
        $mapper_class = $this->mappers[$type] ?? null;
        if ( $mapper_class ) {
            $mapper = app()->make($mapper_class);
            $result = $mapper($element);

            return $result;
        }

        return $element;
    }

    protected function mapList(string $type, array $elements): array
    {
        $mapper_class = $this->mappers[$type] ?? null;
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
