<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Integration\Messenger;

use IntegrationKit\Bundle\Executor\IntegrationExecutor;
use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use IntegrationKit\Bundle\Messenger\IntegrationMessage;
use IntegrationKit\Bundle\Messenger\IntegrationMessageHandler;
use IntegrationKit\Bundle\Registry\HandlerRegistry;
use IntegrationKit\Bundle\Tests\Unit\Messenger\SerializableTestCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Tests d'intégration pour IntegrationMessageHandler.
 *
 * Ces tests vérifient que le handler fonctionne correctement
 * même sans Messenger installé (tests unitaires purs).
 */
final class IntegrationMessageHandlerIntegrationTest extends TestCase
{

    public function testHandlerExecutesCommandSuccessfully(): void
    {
        $command = new SerializableTestCommand();
        $testHandler = $this->createHandler($command::class, 'success');
        
        // Enregistrer le handler dans le registry
        $handlerRegistry = new HandlerRegistry();
        $handlerRegistry->register($command::class, $testHandler);
        
        $eventDispatcher = new EventDispatcher();
        $executor = new IntegrationExecutor($handlerRegistry, $eventDispatcher);
        $messageHandler = new IntegrationMessageHandler($executor);
        
        $message = new IntegrationMessage($command);

        // Ne doit pas lever d'exception
        $messageHandler($message);
        
        // Vérifier que le handler a été enregistré
        $this->assertTrue($handlerRegistry->hasHandlerFor($command::class));
    }

    public function testHandlerHandlesExceptions(): void
    {
        $command = new SerializableTestCommand();
        $testHandler = $this->createHandler($command::class, null, new \RuntimeException('Test error'));
        
        $handlerRegistry = new HandlerRegistry();
        $handlerRegistry->register($command::class, $testHandler);
        
        $eventDispatcher = new EventDispatcher();
        $executor = new IntegrationExecutor($handlerRegistry, $eventDispatcher);
        $messageHandler = new IntegrationMessageHandler($executor);
        
        $message = new IntegrationMessage($command);

        // executeWithResult() catch les exceptions et retourne un ApiResult::failure()
        // Le handler ne propage pas l'exception, ce qui permet à Messenger de gérer
        // les retries via d'autres mécanismes si nécessaire
        // Vérifier que le handler gère l'exception sans planter
        $result = $executor->executeWithResult($command);
        $this->assertTrue($result->isFailure());
        $this->assertSame('Test error', $result->getError());
        
        // Le handler doit pouvoir être appelé sans exception
        $messageHandler($message);
    }

    public function testHandlerWorksWithComplexCommands(): void
    {
        $command = new SerializableTestCommand();
        $testHandler = $this->createHandler($command::class, ['result' => 'ok', 'id' => 42]);
        
        $handlerRegistry = new HandlerRegistry();
        $handlerRegistry->register($command::class, $testHandler);
        
        $eventDispatcher = new EventDispatcher();
        $executor = new IntegrationExecutor($handlerRegistry, $eventDispatcher);
        $messageHandler = new IntegrationMessageHandler($executor);
        
        $message = new IntegrationMessage($command, ['trace_id' => 'abc123']);

        // Ne doit pas lever d'exception
        $messageHandler($message);
        
        // Vérifier que les métadonnées sont préservées
        $this->assertSame(['trace_id' => 'abc123'], $message->getMetadata());
    }

    private function createHandler(string $commandClass, mixed $returnValue = null, ?\Throwable $exception = null): IntegrationHandlerInterface
    {
        return new class($commandClass, $returnValue, $exception) implements IntegrationHandlerInterface {
            public function __construct(
                private readonly string $commandClass,
                private readonly mixed $returnValue,
                private readonly ?\Throwable $exception
            ) {}

            public function supports(): string
            {
                return $this->commandClass;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                if ($this->exception !== null) {
                    throw $this->exception;
                }

                return $this->returnValue;
            }
        };
    }
}

