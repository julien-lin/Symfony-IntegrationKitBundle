<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Messenger;

use IntegrationKit\Bundle\IntegrationCommand;

/**
 * Messenger message to execute an integration command asynchronously.
 *
 * This class encapsulates an IntegrationCommand with optional metadata
 * to enable asynchronous execution via Symfony Messenger.
 *
 * Serialization is handled automatically by PHP for readonly properties.
 * Messenger can serialize/unserialize this class without issues.
 */
final class IntegrationMessage
{
    /**
     * @param IntegrationCommand $command Integration command to execute
     * @param array<string, mixed> $metadata Optional metadata (trace_id, user_id, etc.)
     */
    public function __construct(
        private readonly IntegrationCommand $command,
        private readonly array $metadata = []
    ) {
    }

    /**
     * Returns the integration command.
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
}

