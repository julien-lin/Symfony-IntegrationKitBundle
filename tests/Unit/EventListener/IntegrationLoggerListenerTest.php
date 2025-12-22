<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\EventListener;

use IntegrationKit\Bundle\Event\IntegrationFailureEvent;
use IntegrationKit\Bundle\Event\IntegrationRequestEvent;
use IntegrationKit\Bundle\Event\IntegrationSuccessEvent;
use IntegrationKit\Bundle\EventListener\IntegrationLoggerListener;
use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Tests pour IntegrationLoggerListener.
 */
final class IntegrationLoggerListenerTest extends TestCase
{
    private LoggerInterface $logger;
    private IntegrationLoggerListener $listener;
    private array $loggedMessages = [];

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new IntegrationLoggerListener($this->logger);
        $this->loggedMessages = [];
    }

    public function testListenerListensToRequestEvent(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'slack',
            $command,
            ['trace_id' => 'abc123']
        );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('slack'),
                $this->callback(function (array $context) {
                    $this->loggedMessages[] = ['level' => LogLevel::INFO, 'context' => $context];
                    return $context['integration_name'] === 'slack'
                        && $context['command_class'] === 'App\\Command'
                        && $context['status'] === 'request';
                })
            );

        $this->listener->onIntegrationRequest($event);
    }

    public function testListenerListensToSuccessEvent(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'slack',
            $command,
            'success',
            145.5,
            ['trace_id' => 'abc123']
        );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('slack'),
                $this->callback(function (array $context) {
                    $this->loggedMessages[] = ['level' => LogLevel::INFO, 'context' => $context];
                    return $context['integration_name'] === 'slack'
                        && $context['status'] === 'success'
                        && $context['duration_ms'] === 145.5;
                })
            );

        $this->listener->onIntegrationSuccess($event);
    }

    public function testListenerListensToFailureEvent(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Error message');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'slack',
            $command,
            $exception,
            145.5,
            ['trace_id' => 'abc123']
        );

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('slack'),
                $this->callback(function (array $context) {
                    $this->loggedMessages[] = ['level' => LogLevel::ERROR, 'context' => $context];
                    return $context['integration_name'] === 'slack'
                        && $context['status'] === 'failure'
                        && $context['error'] === 'Error message'
                        && $context['duration_ms'] === 145.5;
                })
            );

        $this->listener->onIntegrationFailure($event);
    }

    public function testLogSuccessContainsAllInformation(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Integration\\Slack\\SlackNotifyCommand',
            'slack',
            $command,
            ['user_id' => 42],
            145.5,
            ['trace_id' => 'abc123']
        );

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->isType('string'),
                $this->callback(function (array $context) {
                    $this->assertSame('slack', $context['integration_name']);
                    $this->assertSame('App\\Integration\\Slack\\SlackNotifyCommand', $context['command_class']);
                    $this->assertSame('success', $context['status']);
                    $this->assertSame(145.5, $context['duration_ms']);
                    $this->assertSame('abc123', $context['metadata']['trace_id']);
                    $this->assertArrayHasKey('timestamp', $context);
                    return true;
                })
            );

        $this->listener->onIntegrationSuccess($event);
    }

    public function testLogFailureContainsError(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Handler error');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'slack',
            $command,
            $exception,
            145.5,
            []
        );

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->isType('string'),
                $this->callback(function (array $context) {
                    $this->assertSame('slack', $context['integration_name']);
                    $this->assertSame('failure', $context['status']);
                    $this->assertSame('Handler error', $context['error']);
                    $this->assertSame(145.5, $context['duration_ms']);
                    $this->assertArrayHasKey('exception_class', $context);
                    return true;
                })
            );

        $this->listener->onIntegrationFailure($event);
    }

    public function testLogIsValidJson(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'slack',
            $command,
            'success',
            145.5,
            ['trace_id' => 'abc123']
        );

        $capturedContext = null;

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->isType('string'),
                $this->callback(function (array $context) use (&$capturedContext) {
                    $capturedContext = $context;
                    return true;
                })
            );

        $this->listener->onIntegrationSuccess($event);

        // Vérifier que le contexte peut être encodé en JSON
        $this->assertNotNull($capturedContext);
        $json = json_encode($capturedContext);
        $this->assertNotFalse($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('slack', $decoded['integration_name']);
    }

    public function testLogContainsTimestamp(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationSuccessEvent(
            'App\\Command',
            'slack',
            $command,
            'success',
            145.5,
            []
        );

        $capturedContext = null;

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->isType('string'),
                $this->callback(function (array $context) use (&$capturedContext) {
                    $capturedContext = $context;
                    return true;
                })
            );

        $this->listener->onIntegrationSuccess($event);

        $this->assertNotNull($capturedContext);
        $this->assertArrayHasKey('timestamp', $capturedContext);
        $this->assertIsString($capturedContext['timestamp']);
        // Vérifier que c'est un format ISO 8601 valide
        $this->assertNotFalse(\DateTime::createFromFormat(\DateTimeInterface::ATOM, $capturedContext['timestamp']));
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

