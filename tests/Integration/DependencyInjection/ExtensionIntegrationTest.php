<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Integration\DependencyInjection;

use IntegrationKit\Bundle\DependencyInjection\IntegrationKitExtension;
use IntegrationKit\Bundle\Executor\IntegrationExecutorInterface;
use IntegrationKit\Bundle\Registry\HandlerRegistryInterface;
use IntegrationKit\Bundle\Registry\IntegrationRegistryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Psr\Log\NullLogger;

/**
 * Tests d'intégration pour IntegrationKitExtension.
 *
 * Vérifie que le bundle peut être chargé et que tous les services
 * sont correctement enregistrés et fonctionnels.
 */
final class ExtensionIntegrationTest extends TestCase
{
    private IntegrationKitExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new IntegrationKitExtension();
        $this->container = new ContainerBuilder();
        
        // Enregistrer les services requis par le bundle
        $this->container->setDefinition('event_dispatcher', new Definition(EventDispatcher::class));
        $this->container->setDefinition('logger', new Definition(NullLogger::class));
    }

    public function testExtensionCanBeLoaded(): void
    {
        // Ne doit pas lever d'exception
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasDefinition('integration_kit.registry.integration'));
    }

    public function testServicesAreRegistered(): void
    {
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasDefinition('integration_kit.registry.integration'));
        $this->assertTrue($this->container->hasDefinition('integration_kit.registry.handler'));
        $this->assertTrue($this->container->hasDefinition('integration_kit.executor'));
        $this->assertTrue($this->container->hasDefinition('integration_kit.event_listener.logger'));
    }

    public function testServicesHaveCorrectAliases(): void
    {
        $this->extension->load([], $this->container);
        
        $this->assertTrue($this->container->hasAlias(IntegrationRegistryInterface::class));
        $this->assertTrue($this->container->hasAlias(HandlerRegistryInterface::class));
        $this->assertTrue($this->container->hasAlias(IntegrationExecutorInterface::class));
    }

    public function testContainerCanBeCompiled(): void
    {
        $this->extension->load([], $this->container);
        
        // Le container doit pouvoir être compilé sans erreur
        $this->container->compile();
        
        $this->assertTrue($this->container->isCompiled());
    }

    public function testServicesCanBeResolvedAfterCompilation(): void
    {
        $this->extension->load([], $this->container);
        
        // Rendre les services publics pour les tests
        $this->container->getDefinition('integration_kit.registry.integration')->setPublic(true);
        $this->container->getDefinition('integration_kit.registry.handler')->setPublic(true);
        $this->container->getDefinition('integration_kit.executor')->setPublic(true);
        
        // Vérifier que les alias sont définis avant compilation
        $this->assertTrue($this->container->hasAlias(IntegrationRegistryInterface::class));
        $this->assertTrue($this->container->hasAlias(HandlerRegistryInterface::class));
        $this->assertTrue($this->container->hasAlias(IntegrationExecutorInterface::class));
        
        $this->container->compile();
        
        // Après compilation, vérifier que les services peuvent être résolus via leurs IDs
        $this->assertTrue($this->container->has('integration_kit.registry.integration'));
        $this->assertTrue($this->container->has('integration_kit.registry.handler'));
        $this->assertTrue($this->container->has('integration_kit.executor'));
        
        // Vérifier que les instances peuvent être créées
        $registry = $this->container->get('integration_kit.registry.integration');
        $this->assertNotNull($registry);
        
        $handlerRegistry = $this->container->get('integration_kit.registry.handler');
        $this->assertNotNull($handlerRegistry);
        
        $executor = $this->container->get('integration_kit.executor');
        $this->assertNotNull($executor);
    }
}

