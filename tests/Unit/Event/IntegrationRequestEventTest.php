<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Event;

use IntegrationKit\Bundle\Event\IntegrationRequestEvent;
use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour IntegrationRequestEvent.
 */
final class IntegrationRequestEventTest extends TestCase
{
    public function testEventContainsAllProperties(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Integration\\Slack\\SlackNotifyCommand',
            'slack',
            $command,
            ['trace_id' => 'abc123']
        );

        $this->assertSame('App\\Integration\\Slack\\SlackNotifyCommand', $event->getCommandClass());
        $this->assertSame('slack', $event->getIntegrationName());
        $this->assertSame($command, $event->getCommand());
        $this->assertSame(['trace_id' => 'abc123'], $event->getMetadata());
    }

    public function testAddMetadataAddsMetadata(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'test',
            $command,
            ['key1' => 'value1']
        );

        $event->addMetadata('key2', 'value2');

        $metadata = $event->getMetadata();
        $this->assertSame('value1', $metadata['key1']);
        $this->assertSame('value2', $metadata['key2']);
    }

    public function testAddMetadataOverwritesExistingKey(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'test',
            $command,
            ['key' => 'value1']
        );

        $event->addMetadata('key', 'value2');

        $this->assertSame('value2', $event->getMetadata()['key']);
    }

    public function testEventIsImmutableExceptMetadata(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'test',
            $command,
            []
        );

        // Vérifier qu'il n'y a pas de setters pour les propriétés immutables
        $reflection = new \ReflectionClass($event);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        $immutableProperties = ['commandClass', 'integrationName', 'command'];
        foreach ($methods as $method) {
            $methodName = $method->getName();
            foreach ($immutableProperties as $property) {
                if (str_starts_with($methodName, 'set' . ucfirst($property))) {
                    $this->fail("Event should not have setter for immutable property: {$property}");
                }
            }
        }

        // Metadata peut être modifiée
        $event->addMetadata('key', 'value');
        $this->assertArrayHasKey('key', $event->getMetadata());
    }

    public function testEventExtendsSymfonyEvent(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'test',
            $command
        );

        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }

    public function testEventWithEmptyMetadata(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'test',
            $command
        );

        $this->assertSame([], $event->getMetadata());
    }

    private function createCommand(): IntegrationCommand
    {
        return new class implements IntegrationCommand {
            public function integrationName(): string
            {
                return 'test';
            }
        };
    }
}

