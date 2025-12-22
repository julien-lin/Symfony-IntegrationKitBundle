<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Registry;

use IntegrationKit\Bundle\Exception\IntegrationNotFoundException;
use IntegrationKit\Bundle\IntegrationInterface;

/**
 * Integration registry.
 *
 * Stores and resolves registered integrations. Does ONLY that.
 * Execution is handled by IntegrationExecutor.
 */
final class IntegrationRegistry implements IntegrationRegistryInterface
{
    /**
     * @var array<string, IntegrationInterface>
     */
    private array $integrations = [];

    /**
     * Registers an integration.
     *
     * @param string $name Integration name
     * @param IntegrationInterface $integration Integration to register
     * @return void
     */
    public function register(string $name, IntegrationInterface $integration): void
    {
        $this->integrations[$name] = $integration;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $name): IntegrationInterface
    {
        if (!$this->has($name)) {
            throw new IntegrationNotFoundException($name, array_keys($this->integrations));
        }

        return $this->integrations[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $name): bool
    {
        return isset($this->integrations[$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->integrations;
    }
}

