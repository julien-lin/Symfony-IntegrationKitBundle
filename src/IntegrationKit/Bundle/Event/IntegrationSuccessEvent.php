<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Event;

use IntegrationKit\Bundle\IntegrationCommand;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched after successful integration execution.
 *
 * @example
 * ```php
 * $event = new IntegrationSuccessEvent(
 *     SlackNotifyCommand::class,
 *     'slack',
 *     $command,
 *     $result,
 *     145.5, // duration_ms
 *     ['trace_id' => 'abc123']
 * );
 * ```
 */
final class IntegrationSuccessEvent extends Event
{
    /**
     * @param string $commandClass Command FQCN
     * @param string $integrationName Integration name
     * @param IntegrationCommand $command Executed command
     * @param mixed $result Execution result (can be void, value, or object)
     * @param float $duration Execution duration in milliseconds
     * @param array<string, mixed> $metadata Metadata
     */
    public function __construct(
        private readonly string $commandClass,
        private readonly string $integrationName,
        private readonly IntegrationCommand $command,
        private readonly mixed $result,
        private readonly float $duration,
        private readonly array $metadata = []
    ) {
    }

    /**
     * Returns the command FQCN.
     *
     * @return string
     */
    public function getCommandClass(): string
    {
        return $this->commandClass;
    }

    /**
     * Returns the integration name.
     *
     * @return string
     */
    public function getIntegrationName(): string
    {
        return $this->integrationName;
    }

    /**
     * Returns the executed command.
     *
     * @return IntegrationCommand
     */
    public function getCommand(): IntegrationCommand
    {
        return $this->command;
    }

    /**
     * Returns the execution result.
     *
     * @return mixed
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Returns the execution duration in milliseconds.
     *
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Returns the metadata.
     *
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}

