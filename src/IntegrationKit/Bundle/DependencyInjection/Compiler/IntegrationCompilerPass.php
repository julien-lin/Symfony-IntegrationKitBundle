<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\DependencyInjection\Compiler;

use IntegrationKit\Bundle\IntegrationHandlerInterface;
use IntegrationKit\Bundle\IntegrationInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to discover and register integrations and handlers.
 *
 * Responsibilities:
 * - Discover services tagged with `integration_kit.integration`
 * - Discover services tagged with `integration_kit.handler`
 * - Validate unique integration names
 * - Validate that each handler supports a valid command
 * - Register in registries
 */
final class IntegrationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('integration_kit.registry.integration')) {
            return;
        }

        if (!$container->hasDefinition('integration_kit.registry.handler')) {
            return;
        }

        $integrationRegistry = $container->getDefinition('integration_kit.registry.integration');
        $handlerRegistry = $container->getDefinition('integration_kit.registry.handler');

        // Discover and register integrations
        $this->processIntegrations($container, $integrationRegistry);

        // Discover and register handlers
        $this->processHandlers($container, $handlerRegistry);
    }

    /**
     * Processes tagged integrations.
     *
     * @param ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Definition $registry
     * @return void
     */
    private function processIntegrations(ContainerBuilder $container, \Symfony\Component\DependencyInjection\Definition $registry): void
    {
        $integrationNames = [];
        $taggedServices = $container->findTaggedServiceIds('integration_kit.integration');

        foreach ($taggedServices as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $name = $tag['name'] ?? null;
                
                if ($name === null) {
                    // Try to get the name from the service itself
                    $definition = $container->getDefinition($serviceId);
                    $class = $definition->getClass();
                    
                    if ($class && is_subclass_of($class, IntegrationInterface::class)) {
                        // To get the name, we need to instantiate the service
                        // But we cannot do that at compilation time
                        // So we require the tag to contain 'name'
                        throw new \RuntimeException(
                            sprintf(
                                'Integration service "%s" must have a "name" attribute in its tag.',
                                $serviceId
                            )
                        );
                    }
                }

                // Validate name uniqueness
                if (isset($integrationNames[$name])) {
                    throw new \RuntimeException(
                        sprintf(
                            'Integration name "%s" is already registered by service "%s". Cannot register it again for service "%s".',
                            $name,
                            $integrationNames[$name],
                            $serviceId
                        )
                    );
                }

                $integrationNames[$name] = $serviceId;

                // Register in the registry
                $registry->addMethodCall('register', [
                    $name,
                    new Reference($serviceId),
                ]);
            }
        }
    }

    /**
     * Processes tagged handlers.
     *
     * @param ContainerBuilder $container
     * @param \Symfony\Component\DependencyInjection\Definition $registry
     * @return void
     */
    private function processHandlers(ContainerBuilder $container, \Symfony\Component\DependencyInjection\Definition $registry): void
    {
        $taggedServices = $container->findTaggedServiceIds('integration_kit.handler');

        foreach ($taggedServices as $serviceId => $tags) {
            $definition = $container->getDefinition($serviceId);
            $class = $definition->getClass();

            if (!$class || !is_subclass_of($class, IntegrationHandlerInterface::class)) {
                throw new \RuntimeException(
                    sprintf(
                        'Handler service "%s" must implement IntegrationHandlerInterface.',
                        $serviceId
                    )
                );
            }

            // To get the supported command, we need to call supports()
            // But we cannot instantiate the service at compilation time
            // So we must use a different approach
            
            // Option 1: Require an attribute in the tag
            $commandClass = null;
            foreach ($tags as $tag) {
                if (isset($tag['command'])) {
                    $commandClass = $tag['command'];
                    break;
                }
            }

            if ($commandClass === null) {
                // Option 2: Use a naming convention or PHP 8 attribute
                // For now, we require the attribute in the tag
                throw new \RuntimeException(
                    sprintf(
                        'Handler service "%s" must have a "command" attribute in its tag specifying the command class it handles.',
                        $serviceId
                    )
                );
            }

            // Validate that the command class exists (optional, but recommended)
            if (!class_exists($commandClass) && !interface_exists($commandClass)) {
                // We still accept it, as the class may be loaded later
                // But we log a warning (not implemented yet)
            }

            // Register in the registry
            $registry->addMethodCall('register', [
                $commandClass,
                new Reference($serviceId),
            ]);
        }
    }
}
