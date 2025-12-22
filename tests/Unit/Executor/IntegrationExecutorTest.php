<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Executor;

use IntegrationKit\Bundle\ApiResult;
use IntegrationKit\Bundle\Event\IntegrationFailureEvent;
use IntegrationKit\Bundle\Event\IntegrationRequestEvent;
use IntegrationKit\Bundle\Event\IntegrationSuccessEvent;
use IntegrationKit\Bundle\Executor\IntegrationExecutor;
use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use IntegrationKit\Bundle\Registry\HandlerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Tests pour IntegrationExecutor.
 */
final class IntegrationExecutorTest extends TestCase
{
    private HandlerRegistry $handlerRegistry;
    private EventDispatcherInterface $eventDispatcher;
    private IntegrationExecutor $executor;

    protected function setUp(): void
    {
        $this->handlerRegistry = new HandlerRegistry();
        $this->eventDispatcher = new EventDispatcher();
        $this->executor = new IntegrationExecutor($this->handlerRegistry, $this->eventDispatcher);
    }

    public function testExecuteResolvesHandlerCorrectly(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, 'success');
        $this->handlerRegistry->register($command::class, $handler);

        $result = $this->executor->execute($command);

        $this->assertSame('success', $result);
    }

    public function testExecuteDispatchesIntegrationRequestEventBeforeCall(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, 'success');
        $this->handlerRegistry->register($command::class, $handler);

        $dispatchedEvents = [];
        $this->eventDispatcher->addListener(IntegrationRequestEvent::class, function (IntegrationRequestEvent $event) use (&$dispatchedEvents) {
            $dispatchedEvents[] = $event;
        });

        $this->executor->execute($command);

        $this->assertCount(1, $dispatchedEvents);
        $this->assertSame($command::class, $dispatchedEvents[0]->getCommandClass());
        $this->assertSame('test', $dispatchedEvents[0]->getIntegrationName());
        $this->assertSame($command, $dispatchedEvents[0]->getCommand());
    }

    public function testExecuteDispatchesIntegrationSuccessEventAfterSuccess(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, 'success');
        $this->handlerRegistry->register($command::class, $handler);

        $dispatchedEvents = [];
        $this->eventDispatcher->addListener(IntegrationSuccessEvent::class, function (IntegrationSuccessEvent $event) use (&$dispatchedEvents) {
            $dispatchedEvents[] = $event;
        });

        $this->executor->execute($command);

        $this->assertCount(1, $dispatchedEvents);
        $this->assertSame($command::class, $dispatchedEvents[0]->getCommandClass());
        $this->assertSame('test', $dispatchedEvents[0]->getIntegrationName());
        $this->assertSame('success', $dispatchedEvents[0]->getResult());
        $this->assertGreaterThanOrEqual(0, $dispatchedEvents[0]->getDuration());
    }

    public function testExecuteDispatchesIntegrationFailureEventAfterFailure(): void
    {
        $command = new TestCommand();
        $exception = new \RuntimeException('Handler error');
        $handler = $this->createHandler($command::class, null, $exception);
        $this->handlerRegistry->register($command::class, $handler);

        $dispatchedEvents = [];
        $this->eventDispatcher->addListener(IntegrationFailureEvent::class, function (IntegrationFailureEvent $event) use (&$dispatchedEvents) {
            $dispatchedEvents[] = $event;
        });

        try {
            $this->executor->execute($command);
            $this->fail('Expected exception');
        } catch (\RuntimeException $e) {
            $this->assertSame('Handler error', $e->getMessage());
        }

        $this->assertCount(1, $dispatchedEvents);
        $this->assertSame($command::class, $dispatchedEvents[0]->getCommandClass());
        $this->assertSame('test', $dispatchedEvents[0]->getIntegrationName());
        $this->assertSame($exception, $dispatchedEvents[0]->getException());
        $this->assertGreaterThan(0, $dispatchedEvents[0]->getDuration());
    }

    public function testExecuteMeasuresExecutionDuration(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, 'success', null, 100); // Simule 100ms
        $this->handlerRegistry->register($command::class, $handler);

        $dispatchedEvents = [];
        $this->eventDispatcher->addListener(IntegrationSuccessEvent::class, function (IntegrationSuccessEvent $event) use (&$dispatchedEvents) {
            $dispatchedEvents[] = $event;
        });

        $this->executor->execute($command);

        $this->assertCount(1, $dispatchedEvents);
        $duration = $dispatchedEvents[0]->getDuration();
        $this->assertIsFloat($duration);
        $this->assertGreaterThanOrEqual(0.1, $duration); // Au moins 100ms
    }

    public function testExecuteReturnsHandlerResult(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, 'result value');
        $this->handlerRegistry->register($command::class, $handler);

        $result = $this->executor->execute($command);

        $this->assertSame('result value', $result);
    }

    public function testExecuteReturnsNullForVoidHandler(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, null);
        $this->handlerRegistry->register($command::class, $handler);

        $result = $this->executor->execute($command);

        $this->assertNull($result);
    }

    public function testExecutePropagatesHandlerExceptions(): void
    {
        $command = new TestCommand();
        $exception = new \RuntimeException('Handler error');
        $handler = $this->createHandler($command::class, null, $exception);
        $this->handlerRegistry->register($command::class, $handler);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler error');

        $this->executor->execute($command);
    }

    public function testExecuteWithResultReturnsApiResultSuccessOnSuccess(): void
    {
        $command = new TestCommand();
        $handler = $this->createHandler($command::class, 'success');
        $this->handlerRegistry->register($command::class, $handler);

        $result = $this->executor->executeWithResult($command);

        $this->assertInstanceOf(ApiResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertSame('success', $result->getData());
    }

    public function testExecuteWithResultReturnsApiResultFailureOnFailure(): void
    {
        $command = new TestCommand();
        $exception = new \RuntimeException('Handler error');
        $handler = $this->createHandler($command::class, null, $exception);
        $this->handlerRegistry->register($command::class, $handler);

        $result = $this->executor->executeWithResult($command);

        $this->assertInstanceOf(ApiResult::class, $result);
        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
        $this->assertStringContainsString('Handler error', $result->getError());
        $this->assertSame($exception, $result->getException());
    }

    public function testExecuteWithResultIncludesDurationInMetadata(): void
    {
        $command = new TestCommand();
        // Utiliser un sleep minimal pour garantir une durée > 0
        $handler = $this->createHandler($command::class, 'success', null, 1);
        $this->handlerRegistry->register($command::class, $handler);

        $result = $this->executor->executeWithResult($command);

        $metadata = $result->getMetadata();
        $this->assertArrayHasKey('duration_ms', $metadata);
        $this->assertIsFloat($metadata['duration_ms']);
        $this->assertGreaterThanOrEqual(0, $metadata['duration_ms']); // Peut être 0 sur certaines machines très rapides
    }

    private function createHandler(string $commandClass, mixed $returnValue = null, ?\Throwable $exception = null, int $sleepMs = 0): IntegrationHandlerInterface
    {
        return new class($commandClass, $returnValue, $exception, $sleepMs) implements IntegrationHandlerInterface {
            public function __construct(
                private readonly string $commandClass,
                private readonly mixed $returnValue,
                private readonly ?\Throwable $exception,
                private readonly int $sleepMs
            ) {}

            public function supports(): string
            {
                return $this->commandClass;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                if ($this->sleepMs > 0) {
                    usleep($this->sleepMs * 1000); // Convertir ms en microsecondes
                }

                if ($this->exception !== null) {
                    throw $this->exception;
                }

                return $this->returnValue;
            }
        };
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

