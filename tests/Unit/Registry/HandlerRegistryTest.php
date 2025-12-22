<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Registry;

use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use IntegrationKit\Bundle\Registry\HandlerRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour HandlerRegistry.
 */
final class HandlerRegistryTest extends TestCase
{
    private HandlerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new HandlerRegistry();
    }

    public function testHasHandlerForReturnsTrueIfHandlerExists(): void
    {
        $handler = $this->createHandler(TestCommand::class);
        $this->registry->register(TestCommand::class, $handler);

        $this->assertTrue($this->registry->hasHandlerFor(TestCommand::class));
    }

    public function testHasHandlerForReturnsFalseIfHandlerDoesNotExist(): void
    {
        $this->assertFalse($this->registry->hasHandlerFor(TestCommand::class));
    }

    public function testGetHandlerForReturnsHandlerIfExists(): void
    {
        $handler = $this->createHandler(TestCommand::class);
        $this->registry->register(TestCommand::class, $handler);

        $result = $this->registry->getHandlerFor(TestCommand::class);

        $this->assertSame($handler, $result);
    }

    public function testGetHandlerForThrowsExceptionIfNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No handler found for command');

        $this->registry->getHandlerFor(TestCommand::class);
    }

    public function testMappingIsConstructedCorrectly(): void
    {
        $handler1 = $this->createHandler(TestCommand::class);
        $handler2 = $this->createHandler(AnotherTestCommand::class);

        $this->registry->register(TestCommand::class, $handler1);
        $this->registry->register(AnotherTestCommand::class, $handler2);

        $this->assertTrue($this->registry->hasHandlerFor(TestCommand::class));
        $this->assertTrue($this->registry->hasHandlerFor(AnotherTestCommand::class));
        $this->assertSame($handler1, $this->registry->getHandlerFor(TestCommand::class));
        $this->assertSame($handler2, $this->registry->getHandlerFor(AnotherTestCommand::class));
    }

    public function testRegisterOverwritesExistingHandler(): void
    {
        $handler1 = $this->createHandler(TestCommand::class);
        $handler2 = $this->createHandler(TestCommand::class);

        $this->registry->register(TestCommand::class, $handler1);
        $this->registry->register(TestCommand::class, $handler2);

        $this->assertSame($handler2, $this->registry->getHandlerFor(TestCommand::class));
    }

    private function createHandler(string $commandClass): IntegrationHandlerInterface
    {
        return new class($commandClass) implements IntegrationHandlerInterface {
            public function __construct(private readonly string $commandClass) {}

            public function supports(): string
            {
                return $this->commandClass;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                return null;
            }
        };
    }
}

/**
 * Commandes de test.
 */
final class TestCommand implements IntegrationCommand
{
    public function integrationName(): string
    {
        return 'test';
    }
}

final class AnotherTestCommand implements IntegrationCommand
{
    public function integrationName(): string
    {
        return 'another';
    }
}

