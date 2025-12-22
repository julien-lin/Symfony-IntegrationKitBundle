<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Event;

use IntegrationKit\Bundle\Event\IntegrationFailureEvent;
use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour IntegrationFailureEvent.
 */
final class IntegrationFailureEventTest extends TestCase
{
    public function testEventContainsAllProperties(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Error message');
        $duration = 145.5;
        $metadata = ['trace_id' => 'abc123'];

        $event = new IntegrationFailureEvent(
            'App\\Integration\\Slack\\SlackNotifyCommand',
            'slack',
            $command,
            $exception,
            $duration,
            $metadata
        );

        $this->assertSame('App\\Integration\\Slack\\SlackNotifyCommand', $event->getCommandClass());
        $this->assertSame('slack', $event->getIntegrationName());
        $this->assertSame($command, $event->getCommand());
        $this->assertSame($exception, $event->getException());
        $this->assertSame($duration, $event->getDuration());
        $this->assertSame($metadata, $event->getMetadata());
    }

    public function testExceptionIsAlwaysPresent(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Error');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'test',
            $command,
            $exception,
            10.0
        );

        $this->assertInstanceOf(\Throwable::class, $event->getException());
        $this->assertSame($exception, $event->getException());
    }

    public function testDurationIsPositiveFloat(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Error');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'test',
            $command,
            $exception,
            42.5,
            []
        );

        $this->assertIsFloat($event->getDuration());
        $this->assertGreaterThan(0, $event->getDuration());
    }

    public function testEventExtendsSymfonyEvent(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Error');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'test',
            $command,
            $exception,
            10.0
        );

        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }

    public function testEventWithEmptyMetadata(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Error');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'test',
            $command,
            $exception,
            10.0
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

