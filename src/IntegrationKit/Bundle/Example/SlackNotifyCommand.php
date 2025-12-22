<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Example;

use IntegrationKit\Bundle\IntegrationCommand;

/**
 * Example command to notify Slack.
 *
 * This example shows how to create a typed command for an external integration.
 */
final class SlackNotifyCommand implements IntegrationCommand
{
    /**
     * @param string $text Message to send to Slack
     * @param array<string, mixed> $blocks Optional blocks for enriched message
     */
    public function __construct(
        public readonly string $text,
        public readonly array $blocks = []
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function integrationName(): string
    {
        return 'slack';
    }
}

