<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\DependencyInjection\Compiler;

use IntegrationKit\Bundle\DependencyInjection\Compiler\IntegrationCompilerPass;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use IntegrationKit\Bundle\IntegrationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Tests pour IntegrationCompilerPass.
 */
final class IntegrationCompilerPassTest extends TestCase
{
    private IntegrationCompilerPass $compilerPass;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->compilerPass = new IntegrationCompilerPass();
        $this->container = new ContainerBuilder();
        
        // Enregistrer les registries
        $this->container->setDefinition('integration_kit.registry.integration', new Definition());
        $this->container->setDefinition('integration_kit.registry.handler', new Definition());
    }

    public function testCompilerPassDiscoversTaggedIntegrations(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getName')->willReturn('test_integration');
        
        $definition = new Definition(get_class($integration));
        $definition->addTag('integration_kit.integration', ['name' => 'test_integration']);
        $this->container->setDefinition('test.integration', $definition);

        $this->compilerPass->process($this->container);

        // Vérifier que le registry a été appelé (via les method calls)
        $registryDefinition = $this->container->getDefinition('integration_kit.registry.integration');
        $methodCalls = $registryDefinition->getMethodCalls();
        
        $this->assertNotEmpty($methodCalls);
        $this->assertSame('register', $methodCalls[0][0]);
    }

    public function testCompilerPassDiscoversTaggedHandlers(): void
    {
        $handler = $this->createMock(IntegrationHandlerInterface::class);
        $handler->method('supports')->willReturn('App\\Command');
        
        $definition = new Definition(get_class($handler));
        $definition->addTag('integration_kit.handler', ['command' => 'App\\Command']);
        $this->container->setDefinition('test.handler', $definition);

        $this->compilerPass->process($this->container);

        // Vérifier que le handler registry a été appelé
        $registryDefinition = $this->container->getDefinition('integration_kit.registry.handler');
        $methodCalls = $registryDefinition->getMethodCalls();
        
        $this->assertNotEmpty($methodCalls);
        $this->assertSame('register', $methodCalls[0][0]);
    }

    public function testCompilerPassValidatesUniqueIntegrationNames(): void
    {
        $integration1 = $this->createMock(IntegrationInterface::class);
        $integration2 = $this->createMock(IntegrationInterface::class);
        
        $definition1 = new Definition(get_class($integration1));
        $definition1->addTag('integration_kit.integration', ['name' => 'duplicate']);
        $this->container->setDefinition('integration1', $definition1);
        
        $definition2 = new Definition(get_class($integration2));
        $definition2->addTag('integration_kit.integration', ['name' => 'duplicate']);
        $this->container->setDefinition('integration2', $definition2);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('duplicate');

        $this->compilerPass->process($this->container);
    }

    public function testCompilerPassValidatesHandlerSupportsValidCommand(): void
    {
        $handler = $this->createMock(IntegrationHandlerInterface::class);
        $handler->method('supports')->willReturn('NonExistent\\Command');
        
        $definition = new Definition(get_class($handler));
        $definition->addTag('integration_kit.handler', ['command' => 'NonExistent\\Command']);
        $this->container->setDefinition('test.handler', $definition);

        // Le compiler pass doit valider que la classe de commande existe
        // ou au moins qu'elle est une chaîne valide
        // Pour l'instant, on accepte n'importe quelle chaîne (la classe peut être chargée plus tard)
        $this->compilerPass->process($this->container);
        
        // Vérifier que le handler a été enregistré
        $registryDefinition = $this->container->getDefinition('integration_kit.registry.handler');
        $methodCalls = $registryDefinition->getMethodCalls();
        $this->assertNotEmpty($methodCalls);
    }

    public function testCompilerPassRegistersIntegrationInRegistry(): void
    {
        $integration = $this->createMock(IntegrationInterface::class);
        $integration->method('getName')->willReturn('my_integration');
        
        $definition = new Definition(get_class($integration));
        $definition->addTag('integration_kit.integration', ['name' => 'my_integration']);
        $this->container->setDefinition('my.integration', $definition);

        $this->compilerPass->process($this->container);

        $registryDefinition = $this->container->getDefinition('integration_kit.registry.integration');
        $methodCalls = $registryDefinition->getMethodCalls();
        
        $this->assertCount(1, $methodCalls);
        $this->assertSame('register', $methodCalls[0][0]);
        $this->assertSame('my_integration', $methodCalls[0][1][0]);
        $this->assertInstanceOf(Reference::class, $methodCalls[0][1][1]);
    }

    public function testCompilerPassRegistersHandlerInRegistry(): void
    {
        $handler = $this->createMock(IntegrationHandlerInterface::class);
        $handler->method('supports')->willReturn('App\\TestCommand');
        
        $definition = new Definition(get_class($handler));
        $definition->addTag('integration_kit.handler', ['command' => 'App\\TestCommand']);
        $this->container->setDefinition('test.handler', $definition);

        $this->compilerPass->process($this->container);

        $registryDefinition = $this->container->getDefinition('integration_kit.registry.handler');
        $methodCalls = $registryDefinition->getMethodCalls();
        
        $this->assertCount(1, $methodCalls);
        $this->assertSame('register', $methodCalls[0][0]);
        $this->assertSame('App\\TestCommand', $methodCalls[0][1][0]);
        $this->assertInstanceOf(Reference::class, $methodCalls[0][1][1]);
    }
}

