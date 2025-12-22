<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Event;

use IntegrationKit\Bundle\IntegrationCommand;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched before integration execution.
 *
 * This event is observable only (no command mutation).
 * Listeners can add metadata (trace_id, retry_count, etc.)
 * via `addMetadata()`.
 *
 * @example
 * ```php
 * $event = new IntegrationRequestEvent(
 *     SlackNotifyCommand::class,
 *     'slack',
 *     $command,
 *     ['trace_id' => 'abc123']
 * );
 * ```
 */
final class IntegrationRequestEvent extends Event
{
    /**
     * @param string $commandClass Command FQCN
     * @param string $integrationName Integration name
     * @param IntegrationCommand $command Command to execute
     * @param array<string, mixed> $metadata Metadata (trace_id, retry_count, etc.)
     */
    public function __construct(
        private readonly string $commandClass,
        private readonly string $integrationName,
        private readonly IntegrationCommand $command,
        private array $metadata = []
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
     * Returns the command to execute.
     *
     * @return IntegrationCommand
     */
    public function getCommand(): IntegrationCommand
    {
        return $this->command;
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

    /**
     * Adds metadata (for listeners).
     *
     * @param string $key Metadata key
     * @param mixed $value Metadata value
     * @return void
     */
    public function addMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }
}

