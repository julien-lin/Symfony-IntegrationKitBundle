<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Example;

use IntegrationKit\Bundle\IntegrationInterface;

/**
 * Example Slack integration.
 *
 * Represents the logical aggregate "Slack" that groups multiple handlers.
 * This class is optional but useful for organization.
 */
final class SlackIntegration implements IntegrationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'slack';
    }
}

