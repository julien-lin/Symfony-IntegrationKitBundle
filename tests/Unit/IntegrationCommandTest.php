<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit;

use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour l'interface IntegrationCommand.
 */
final class IntegrationCommandTest extends TestCase
{
    public function testCommandMustImplementIntegrationName(): void
    {
        $command = new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'test';
            }
        };

        $this->assertSame('test', $command->integrationName());
    }

    public function testIntegrationNameReturnsNonEmptyString(): void
    {
        $command = new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'slack';
            }
        };

        $name = $command->integrationName();

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function testCommandIsSerializable(): void
    {
        $command = new SerializableTestCommand('test@example.com');

        $serialized = serialize($command);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(IntegrationCommand::class, $unserialized);
        $this->assertSame('test@example.com', $unserialized->email);
        $this->assertSame('test', $unserialized->integrationName());
    }
}

/**
 * Classe de test pour la sérialisation.
 */
final class SerializableTestCommand implements IntegrationCommand
{
    public function __construct(
        public readonly string $email = 'test@example.com'
    ) {}

    public function integrationName(): string
    {
        return 'test';
    }
}

