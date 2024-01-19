<?php

namespace Ebolution\Core\Infrastructure\Repositories;

use Ebolution\Core\Domain\Contracts\ProcessTimerInterface;
use Ebolution\Logger\Domain\LoggerFactoryInterface;
use Ebolution\Logger\Infrastructure\Logger;
use Ebolution\LoggerDb\Infrastructure\Models\LogSync;
use ReflectionObject;

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

        /*** begin log DB ***/
        // Remove this block when LOGSTASH is ready
        try {
            // getPrefix is a private method
            $loggerReflection = new ReflectionObject($this->logger);
            $builderProperty = $loggerReflection->getProperty('builder');
            $builderProperty->setAccessible(true);
            $builder = $builderProperty->getValue($this->logger);
            $prefix = $builder->getPrefix();

            LogSync::create([
                'type' => strtolower($prefix),
                'process' => $this->processName,
                'identifier' => $this->identifier,
                'execution_time' => $executionTime
            ]);
        } catch (\Throwable $th) {
            $this->logger->__invoke(
                'LogSync: Error saving process log in database'
            );
        }
        /*** end log DB ***/
    }
}
