<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for IntegrationKitBundle.
 *
 * Minimal configuration - the bundle works without YAML configuration.
 * This class allows validation of configuration if provided.
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('integration_kit');

        $rootNode = $treeBuilder->getRootNode();

        // Minimal configuration - no required parameters
        // The bundle works with default values
        $rootNode
            ->children()
                // No configuration required for now
                // Can be extended later if needed
            ->end();

        return $treeBuilder;
    }
}

