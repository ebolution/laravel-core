<?php

namespace Ebolution\Core\Domain\Contracts;

interface ProcessTimerInterface
{
    public function start(string $processName = ''): void;
    public function stop(): void;
}