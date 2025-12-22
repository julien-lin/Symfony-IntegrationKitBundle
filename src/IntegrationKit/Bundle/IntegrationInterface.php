<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle;

/**
 * Represents an external integration as a logical aggregate.
 *
 * An integration is a logical aggregate (e.g. "Slack", "Stripe") that groups
 * multiple business operations. The name enables registration in the registry
 * and identification in logs/metrics.
 *
 * This interface does NOT define an execution method. Execution is done
 * via command handlers (see IntegrationHandlerInterface).
 *
 * @example
 * ```php
 * final class SlackIntegration implements IntegrationInterface
 * {
 *     public function getName(): string
 *     {
 *         return 'slack';
 *     }
 * }
 * ```
 */
interface IntegrationInterface
{
    /**
     * Returns the unique integration name.
     *
     * @return string The integration name (e.g. 'slack', 'stripe')
     */
    public function getName(): string;
}

