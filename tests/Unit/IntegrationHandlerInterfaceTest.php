<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit;

use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour l'interface IntegrationHandlerInterface.
 */
final class IntegrationHandlerInterfaceTest extends TestCase
{
    public function testHandlerMustImplementSupports(): void
    {
        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string
            {
                return TestCommand::class;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                return null;
            }
        };

        $this->assertSame(TestCommand::class, $handler->supports());
    }

    public function testSupportsReturnsValidFQCN(): void
    {
        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string
            {
                return 'IntegrationKit\\Bundle\\Tests\\Unit\\TestCommand';
            }

            public function handle(IntegrationCommand $command): mixed
            {
                return null;
            }
        };

        $fqcn = $handler->supports();

        $this->assertIsString($fqcn);
        $this->assertTrue(class_exists($fqcn) || interface_exists($fqcn));
    }

    public function testHandleAcceptsIntegrationCommand(): void
    {
        $command = new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'test';
            }
        };

        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string
            {
                return TestCommand::class;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                return 'success';
            }
        };

        $result = $handler->handle($command);

        $this->assertSame('success', $result);
    }

    public function testHandlerCanReturnVoid(): void
    {
        $command = new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'test';
            }
        };

        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string
            {
                return TestCommand::class;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                // Do nothing, returns null (void)
                return null;
            }
        };

        $result = $handler->handle($command);

        $this->assertNull($result);
    }

    public function testHandlerCanReturnValue(): void
    {
        $command = new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'test';
            }
        };

        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string
            {
                return TestCommand::class;
            }

            public function handle(IntegrationCommand $command): string
            {
                return 'result';
            }
        };

        $result = $handler->handle($command);

        $this->assertSame('result', $result);
    }

    public function testHandlerCanThrowException(): void
    {
        $command = new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'test';
            }
        };

        $handler = new class implements IntegrationHandlerInterface {
            public function supports(): string
            {
                return TestCommand::class;
            }

            public function handle(IntegrationCommand $command): mixed
            {
                throw new \RuntimeException('Handler error');
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler error');

        $handler->handle($command);
    }
}

/**
 * Classe de test pour les handlers.
 */
final class TestCommand implements IntegrationCommand
{
    public function integrationName(): string
    {
        return 'test';
    }
}

