<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Registry;

use IntegrationKit\Bundle\IntegrationHandlerInterface;

/**
 * Handler registry.
 *
 * Builds explicit mapping [command_class => handler_service] at compilation time.
 * If a command has no handler, an exception is thrown.
 */
final class HandlerRegistry implements HandlerRegistryInterface
{
    /**
     * @var array<string, IntegrationHandlerInterface>
     */
    private array $handlers = [];

    /**
     * Registers a handler for a command class.
     *
     * @param string $commandClass Command FQCN
     * @param IntegrationHandlerInterface $handler Handler to register
     * @return void
     */
    public function register(string $commandClass, IntegrationHandlerInterface $handler): void
    {
        $this->handlers[$commandClass] = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandlerFor(string $commandClass): IntegrationHandlerInterface
    {
        if (!$this->hasHandlerFor($commandClass)) {
            throw new \RuntimeException(
                sprintf('No handler found for command "%s".', $commandClass)
            );
        }

        return $this->handlers[$commandClass];
    }

    /**
     * {@inheritDoc}
     */
    public function hasHandlerFor(string $commandClass): bool
    {
        return isset($this->handlers[$commandClass]);
    }
}

