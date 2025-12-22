<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle;

use IntegrationKit\Bundle\DependencyInjection\Compiler\IntegrationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * IntegrationKitBundle
 *
 * Standardizes external API consumption in Symfony.
 */
final class IntegrationKitBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new IntegrationCompilerPass());
    }
}

