<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle;

/**
 * Typed execution contract for an external integration.
 *
 * Each command represents a business operation to execute on an integration.
 * The command must expose the target integration name and can contain
 * typed properties for the operation data.
 *
 * @example
 * ```php
 * final class SlackNotifyCommand implements IntegrationCommand
 * {
 *     public function __construct(
 *         public readonly string $email,
 *         public readonly string $name
 *     ) {}
 *
 *     public function integrationName(): string
 *     {
 *         return 'slack';
 *     }
 * }
 * ```
 */
interface IntegrationCommand
{
    /**
     * Returns the target integration name.
     *
     * @return string The integration name (e.g. 'slack', 'stripe')
     */
    public function integrationName(): string;
}

