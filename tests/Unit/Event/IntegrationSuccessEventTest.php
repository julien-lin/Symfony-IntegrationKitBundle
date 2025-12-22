<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Event;

use IntegrationKit\Bundle\Event\IntegrationSuccessEvent;
use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour IntegrationSuccessEvent.
 */
final class IntegrationSuccessEventTest extends TestCase
{
    public function testEventContainsAllProperties(): void
    {
        $command = $this->createCommand();
        $result = 'success result';
        $duration = 145.5;
        $metadata = ['trace_id' => 'abc123'];

        $event = new IntegrationSuccessEvent(
            'App\\Integration\\Slack\\SlackNotifyCommand',
            'slack',
            $command,
            $result,
            $duration,
            $metadata
        );

        $this->assertSame('App\\Integration\\Slack\\SlackNotifyCommand', $event->getCommandClass());
        $this->assertSame('slack', $event->getIntegrationName());
        $this->assertSame($command, $event->getCommand());
        $this->assertSame($result, $event->getResult());
        $this->assertSame($duration, $event->getDuration());
        $this->assertSame($metadata, $event->getMetadata());
    }

    public function testDurationIsPositiveFloat(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'test',
            $command,
            null,
            42.5,
            []
        );

        $this->assertIsFloat($event->getDuration());
        $this->assertGreaterThan(0, $event->getDuration());
    }

    public function testResultCanBeVoid(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'test',
            $command,
            null,
            10.0,
            []
        );

        $this->assertNull($event->getResult());
    }

    public function testResultCanBeValue(): void
    {
        $command = $this->createCommand();
        $result = ['user_id' => 42];
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'test',
            $command,
            $result,
            10.0,
            []
        );

        $this->assertSame($result, $event->getResult());
    }

    public function testResultCanBeObject(): void
    {
        $command = $this->createCommand();
        $result = new \stdClass();
        $result->property = 'value';
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'test',
            $command,
            $result,
            10.0,
            []
        );

        $this->assertSame($result, $event->getResult());
    }

    public function testEventExtendsSymfonyEvent(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'test',
            $command,
            null,
            10.0
        );

        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
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

