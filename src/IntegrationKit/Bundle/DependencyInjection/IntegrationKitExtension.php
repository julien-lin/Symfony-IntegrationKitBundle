<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\DependencyInjection;

use IntegrationKit\Bundle\EventListener\IntegrationLoggerListener;
use IntegrationKit\Bundle\Executor\IntegrationExecutor;
use IntegrationKit\Bundle\Executor\IntegrationExecutorInterface;
use IntegrationKit\Bundle\Messenger\IntegrationMessageHandler;
use IntegrationKit\Bundle\Registry\HandlerRegistry;
use IntegrationKit\Bundle\Registry\HandlerRegistryInterface;
use IntegrationKit\Bundle\Registry\IntegrationRegistry;
use IntegrationKit\Bundle\Registry\IntegrationRegistryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Symfony Extension for IntegrationKitBundle.
 *
 * Registers all bundle services:
 * - IntegrationRegistry
 * - HandlerRegistry
 * - IntegrationExecutor
 * - IntegrationLoggerListener
 * - IntegrationMessageHandler (if Messenger available)
 */
final class IntegrationKitExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register IntegrationRegistry
        $this->registerIntegrationRegistry($container);

        // Register HandlerRegistry
        $this->registerHandlerRegistry($container);

        // Register IntegrationExecutor
        $this->registerExecutor($container);

        // Register IntegrationLoggerListener
        $this->registerLoggerListener($container);

        // Register IntegrationMessageHandler if Messenger is available
        $this->registerMessageHandler($container);
    }

    /**
     * Registers IntegrationRegistry.
     */
    private function registerIntegrationRegistry(ContainerBuilder $container): void
    {
        $definition = new Definition(IntegrationRegistry::class);
        $definition->setPublic(false);
        $container->setDefinition('integration_kit.registry.integration', $definition);
        $container->setAlias(IntegrationRegistryInterface::class, 'integration_kit.registry.integration');
    }

    /**
     * Registers HandlerRegistry.
     */
    private function registerHandlerRegistry(ContainerBuilder $container): void
    {
        $definition = new Definition(HandlerRegistry::class);
        $definition->setPublic(false);
        $container->setDefinition('integration_kit.registry.handler', $definition);
        $container->setAlias(HandlerRegistryInterface::class, 'integration_kit.registry.handler');
    }

    /**
     * Registers IntegrationExecutor.
     */
    private function registerExecutor(ContainerBuilder $container): void
    {
        $definition = new Definition(IntegrationExecutor::class);
        $definition->setArguments([
            new Reference(HandlerRegistryInterface::class),
            new Reference('event_dispatcher'),
        ]);
        $definition->setPublic(false);
        $container->setDefinition('integration_kit.executor', $definition);
        $container->setAlias(IntegrationExecutorInterface::class, 'integration_kit.executor');
    }

    /**
     * Registers IntegrationLoggerListener.
     */
    private function registerLoggerListener(ContainerBuilder $container): void
    {
        $definition = new Definition(IntegrationLoggerListener::class);
        $definition->setArguments([
            new Reference('logger'),
        ]);
        $definition->addTag('kernel.event_subscriber');
        $definition->setPublic(false);
        $container->setDefinition('integration_kit.event_listener.logger', $definition);
    }

    /**
     * Registers IntegrationMessageHandler if Messenger is available.
     */
    private function registerMessageHandler(ContainerBuilder $container): void
    {
        // Check if Messenger is available
        if (!class_exists(\Symfony\Component\Messenger\Handler\MessageHandlerInterface::class)) {
            return;
        }

        $definition = new Definition(IntegrationMessageHandler::class);
        $definition->setArguments([
            new Reference(IntegrationExecutorInterface::class),
        ]);
        $definition->addTag('messenger.message_handler');
        $definition->setPublic(false);
        $container->setDefinition('integration_kit.messenger.handler', $definition);
    }
}

