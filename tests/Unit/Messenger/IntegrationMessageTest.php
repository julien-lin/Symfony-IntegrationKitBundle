<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Messenger;

use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\Messenger\IntegrationMessage;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour IntegrationMessage.
 */
final class IntegrationMessageTest extends TestCase
{
    public function testMessageContainsCommand(): void
    {
        $command = $this->createCommand();
        $message = new IntegrationMessage($command);

        $this->assertSame($command, $message->getCommand());
    }

    public function testMessageContainsMetadata(): void
    {
        $command = $this->createCommand();
        $metadata = ['trace_id' => 'abc123', 'user_id' => 42];
        $message = new IntegrationMessage($command, $metadata);

        $this->assertSame($metadata, $message->getMetadata());
    }

    public function testMessageHasEmptyMetadataByDefault(): void
    {
        $command = $this->createCommand();
        $message = new IntegrationMessage($command);

        $this->assertSame([], $message->getMetadata());
    }

    public function testMessageIsSerializable(): void
    {
        $command = $this->createCommand();
        $metadata = ['trace_id' => 'abc123'];
        $message = new IntegrationMessage($command, $metadata);

        $serialized = serialize($message);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(IntegrationMessage::class, $unserialized);
    }

    public function testMessagePreservesCommandAfterSerialization(): void
    {
        $command = $this->createCommand();
        $message = new IntegrationMessage($command);

        $serialized = serialize($message);
        $unserialized = unserialize($serialized);

        $this->assertInstanceOf(IntegrationCommand::class, $unserialized->getCommand());
        $this->assertSame($command->integrationName(), $unserialized->getCommand()->integrationName());
    }

    public function testMessagePreservesMetadataAfterSerialization(): void
    {
        $command = $this->createCommand();
        $metadata = ['trace_id' => 'abc123', 'user_id' => 42];
        $message = new IntegrationMessage($command, $metadata);

        $serialized = serialize($message);
        $unserialized = unserialize($serialized);

        $this->assertSame($metadata, $unserialized->getMetadata());
    }

    public function testMessageUsesPhp81SerializeMethods(): void
    {
        $command = $this->createCommand();
        $metadata = ['key' => 'value'];
        $message = new IntegrationMessage($command, $metadata);

        // Vérifier que __serialize() et __unserialize() sont utilisés
        $serialized = serialize($message);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);
        $this->assertInstanceOf(IntegrationMessage::class, $unserialized);
        $this->assertSame($metadata, $unserialized->getMetadata());
    }

    public function testMessageWithComplexMetadata(): void
    {
        $command = $this->createCommand();
        $metadata = [
            'trace_id' => 'abc123',
            'user' => ['id' => 42, 'name' => 'John'],
            'tags' => ['important', 'urgent'],
        ];
        $message = new IntegrationMessage($command, $metadata);

        $serialized = serialize($message);
        $unserialized = unserialize($serialized);

        $this->assertSame($metadata, $unserialized->getMetadata());
    }

    private function createCommand(): IntegrationCommand
    {
        return new SerializableTestCommand();
    }
}

