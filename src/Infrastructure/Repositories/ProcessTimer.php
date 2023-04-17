<?php

namespace Ebolution\Core\Infrastructure\Repositories;

use Ebolution\Core\Domain\Contracts\ProcessTimerInterface;
use Ebolution\Logger\Domain\LoggerFactoryInterface;
use Ebolution\Logger\Infrastructure\Logger;

class ProcessTimer implements ProcessTimerInterface
{
    private const BASE_LOG_MESSAGE = 'Process ID: %process_id% (%process_name%) %step% %execution_time%';

    private Logger $logger;
    private int $identifier;
    private string $processName;
    private int $startTime;

    public function __construct(
        LoggerFactoryInterface $loggerFactory
    ) {
        $this->logger = $loggerFactory->create();
    }

    public function start(string $processName = ''): void
    {
        $this->identifier = time();
        $this->processName = $processName;
        $this->startTime = microtime(true);
        $this->logger->__invoke(
            str_replace(
                ['%process_name%', '%step%', '%process_id%', '%execution_time%'],
                [$this->processName, 'started', $this->identifier, ''],
                self::BASE_LOG_MESSAGE
            )
        );
    }

    public function stop(): void
    {
        $executionTime = microtime(true) - $this->startTime;
        $this->logger->__invoke(
            str_replace(
                ['%process_name%', '%step%', '%process_id%', '%execution_time%'],
                [$this->processName, 'finished', $this->identifier, '- Execution time: ' . $executionTime],
                self::BASE_LOG_MESSAGE
            )
        );
    }
}