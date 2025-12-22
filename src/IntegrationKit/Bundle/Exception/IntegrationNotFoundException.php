<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Exception;

/**
 * Exception thrown when an integration is not found in the registry.
 *
 * @example
 * ```php
 * throw new IntegrationNotFoundException('slack', ['stripe', 'crm']);
 * ```
 */
final class IntegrationNotFoundException extends \RuntimeException
{
    /**
     * @param string $integrationName Name of the integration not found
     * @param array<string> $available List of available integrations (optional)
     */
    public function __construct(
        private readonly string $integrationName,
        private readonly array $available = []
    ) {
        $message = sprintf(
            'Integration "%s" not found.',
            $integrationName
        );

        if (!empty($available)) {
            $message .= sprintf(
                ' Available integrations: %s.',
                implode(', ', $available)
            );
        }

        parent::__construct($message);
    }

    /**
     * Returns the name of the integration not found.
     *
     * @return string
     */
    public function getIntegrationName(): string
    {
        return $this->integrationName;
    }

    /**
     * Returns the list of available integrations.
     *
     * @return array<string>
     */
    public function getAvailableIntegrations(): array
    {
        return $this->available;
    }
}

