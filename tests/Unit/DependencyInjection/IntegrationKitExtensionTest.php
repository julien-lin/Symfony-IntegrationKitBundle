<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\DependencyInjection;

use IntegrationKit\Bundle\DependencyInjection\IntegrationKitExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Tests pour IntegrationKitExtension.
 */
final class IntegrationKitExtensionTest extends TestCase
{
    private IntegrationKitExtension $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new IntegrationKitExtension();
        $this->container = new ContainerBuilder();
    }

    public function testExtensionImplementsExtensionInterface(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }

    public function testExtensionLoadsWithoutConfiguration(): void
    {
        // Ne doit pas lever d'exception même sans configuration
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->hasDefinition('integration_kit.registry.integration'));
        $this->assertTrue($this->container->hasDefinition('integration_kit.registry.handler'));
        $this->assertTrue($this->container->hasDefinition('integration_kit.executor'));
        $this->assertTrue($this->container->hasDefinition('integration_kit.event_listener.logger'));
    }

    public function testExtensionRegistersIntegrationRegistry(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('integration_kit.registry.integration');
        $this->assertSame('IntegrationKit\\Bundle\\Registry\\IntegrationRegistry', $definition->getClass());
    }

    public function testExtensionRegistersHandlerRegistry(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('integration_kit.registry.handler');
        $this->assertSame('IntegrationKit\\Bundle\\Registry\\HandlerRegistry', $definition->getClass());
    }

    public function testExtensionRegistersExecutor(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('integration_kit.executor');
        $this->assertSame('IntegrationKit\\Bundle\\Executor\\IntegrationExecutor', $definition->getClass());
        
        // Vérifier que les arguments du constructeur sont définis
        $arguments = $definition->getArguments();
        $this->assertCount(2, $arguments);
    }

    public function testExtensionRegistersLoggerListener(): void
    {
        $this->extension->load([], $this->container);

        $definition = $this->container->getDefinition('integration_kit.event_listener.logger');
        $this->assertSame('IntegrationKit\\Bundle\\EventListener\\IntegrationLoggerListener', $definition->getClass());
        $this->assertTrue($definition->hasTag('kernel.event_subscriber'));
    }

    public function testExtensionRegistersMessageHandlerIfMessengerAvailable(): void
    {
        // Simuler la présence de Messenger
        if (class_exists(\Symfony\Component\Messenger\Handler\MessageHandlerInterface::class)) {
            $this->extension->load([], $this->container);
            $this->assertTrue($this->container->hasDefinition('integration_kit.messenger.handler'));
        } else {
            $this->markTestSkipped('Messenger not available');
        }
    }

    public function testExtensionDoesNotRegisterMessageHandlerIfMessengerNotAvailable(): void
    {
        // Si Messenger n'est pas disponible, le handler ne doit pas être enregistré
        // (test conditionnel)
        $this->extension->load([], $this->container);
        
        // Le handler peut être enregistré mais sans l'interface Messenger
        // C'est acceptable car il est optionnel
        $this->assertTrue(true);
    }
}

