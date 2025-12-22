<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Integration\DependencyInjection\Compiler;

use IntegrationKit\Bundle\DependencyInjection\Compiler\IntegrationCompilerPass;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use IntegrationKit\Bundle\IntegrationInterface;
use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Psr\Log\NullLogger;

/**
 * Tests d'intégration pour IntegrationCompilerPass.
 */
final class IntegrationCompilerPassIntegrationTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        
        // Enregistrer les registries
        $this->container->setDefinition('integration_kit.registry.integration', new Definition());
        $this->container->setDefinition('integration_kit.registry.handler', new Definition());
        
        // Services requis
        $this->container->setDefinition('event_dispatcher', new Definition(EventDispatcher::class));
        $this->container->setDefinition('logger', new Definition(NullLogger::class));
    }

    public function testCompilerPassWorksWithRealServices(): void
    {
        // Créer une intégration réelle
        $integration = new class implements IntegrationInterface {
            public function getName(): string { return 'test_integration'; }
        };
        
        $integrationDefinition = new Definition(get_class($integration));
        $integrationDefinition->addTag('integration_kit.integration', ['name' => 'test_integration']);
        $this->container->setDefinition('test.integration', $integrationDefinition);

        // Créer un handler réel
        $commandClass = TestCommand::class;
        $handler = new class($commandClass) implements IntegrationHandlerInterface {
            public function __construct(private readonly string $commandClass) {}
            public function supports(): string { return $this->commandClass; }
            public function handle(IntegrationCommand $command): mixed { return 'success'; }
        };
        
        $handlerDefinition = new Definition(get_class($handler));
        $handlerDefinition->setArguments([$commandClass]);
        $handlerDefinition->addTag('integration_kit.handler', ['command' => $commandClass]);
        $this->container->setDefinition('test.handler', $handlerDefinition);

        $compilerPass = new IntegrationCompilerPass();
        $compilerPass->process($this->container);

        // Vérifier que les registries ont été mis à jour
        $integrationRegistry = $this->container->getDefinition('integration_kit.registry.integration');
        $handlerRegistry = $this->container->getDefinition('integration_kit.registry.handler');
        
        $this->assertNotEmpty($integrationRegistry->getMethodCalls());
        $this->assertNotEmpty($handlerRegistry->getMethodCalls());
    }

    public function testCompilerPassThrowsExceptionOnDuplicateIntegrationNames(): void
    {
        $integration1 = new class implements IntegrationInterface {
            public function getName(): string { return 'duplicate'; }
        };
        
        $integration2 = new class implements IntegrationInterface {
            public function getName(): string { return 'duplicate'; }
        };
        
        $definition1 = new Definition(get_class($integration1));
        $definition1->addTag('integration_kit.integration', ['name' => 'duplicate']);
        $this->container->setDefinition('integration1', $definition1);
        
        $definition2 = new Definition(get_class($integration2));
        $definition2->addTag('integration_kit.integration', ['name' => 'duplicate']);
        $this->container->setDefinition('integration2', $definition2);

        $compilerPass = new IntegrationCompilerPass();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('duplicate');

        $compilerPass->process($this->container);
    }

    public function testCompilerPassThrowsExceptionOnMissingCommandAttribute(): void
    {
        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string { return 'App\\Command'; }
            public function handle(IntegrationCommand $command): mixed { return null; }
        };
        
        $handlerDefinition = new Definition(get_class($handler));
        $handlerDefinition->addTag('integration_kit.handler'); // Pas d'attribut 'command'
        $this->container->setDefinition('test.handler', $handlerDefinition);

        $compilerPass = new IntegrationCompilerPass();
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('command');

        $compilerPass->process($this->container);
    }
}

/**
 * Commande de test.
 */
final class TestCommand implements IntegrationCommand
{
    public function integrationName(): string
    {
        return 'test';
    }
}

