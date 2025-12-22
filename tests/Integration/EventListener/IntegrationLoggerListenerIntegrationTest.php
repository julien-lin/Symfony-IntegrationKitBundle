<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Integration\EventListener;

use IntegrationKit\Bundle\Event\IntegrationFailureEvent;
use IntegrationKit\Bundle\Event\IntegrationRequestEvent;
use IntegrationKit\Bundle\Event\IntegrationSuccessEvent;
use IntegrationKit\Bundle\EventListener\IntegrationLoggerListener;
use IntegrationKit\Bundle\IntegrationCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Tests d'intégration pour IntegrationLoggerListener avec EventDispatcher réel.
 */
final class IntegrationLoggerListenerIntegrationTest extends TestCase
{
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;
    private IntegrationLoggerListener $listener;
    private array $loggedMessages = [];

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->listener = new IntegrationLoggerListener($this->logger);
        $this->loggedMessages = [];

        // Enregistrer le listener
        $this->dispatcher->addSubscriber($this->listener);

        // Capturer les logs
        $this->logger->method('info')->willReturnCallback(function ($message, $context) {
            $this->loggedMessages[] = [
                'level' => LogLevel::INFO,
                'message' => $message,
                'context' => $context,
            ];
        });

        $this->logger->method('error')->willReturnCallback(function ($message, $context) {
            $this->loggedMessages[] = [
                'level' => LogLevel::ERROR,
                'message' => $message,
                'context' => $context,
            ];
        });
    }

    public function testListenerIsRegisteredAsSubscriber(): void
    {
        $subscribedEvents = IntegrationLoggerListener::getSubscribedEvents();
        $this->assertArrayHasKey(IntegrationRequestEvent::class, $subscribedEvents);
        $this->assertArrayHasKey(IntegrationSuccessEvent::class, $subscribedEvents);
        $this->assertArrayHasKey(IntegrationFailureEvent::class, $subscribedEvents);
    }

    public function testListenerReceivesRequestEventViaDispatcher(): void
    {
        $command = $this->createCommand();
        $event = new IntegrationRequestEvent(
            'App\\Command',
            'slack',
            $command,
            ['trace_id' => 'abc123']
        );

        $this->dispatcher->dispatch($event);

        $this->assertCount(1, $this->loggedMessages);
        $this->assertSame(LogLevel::INFO, $this->loggedMessages[0]['level']);
        $this->assertStringContainsString('slack', $this->loggedMessages[0]['message']);
        $this->assertSame('request', $this->loggedMessages[0]['context']['status']);
    }

    public function testListenerReceivesSuccessEventViaDispatcher(): void
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

        $this->dispatcher->dispatch($event);

        $this->assertCount(1, $this->loggedMessages);
        $this->assertSame(LogLevel::INFO, $this->loggedMessages[0]['level']);
        $this->assertStringContainsString('slack', $this->loggedMessages[0]['message']);
        $this->assertSame('success', $this->loggedMessages[0]['context']['status']);
        $this->assertSame(145.5, $this->loggedMessages[0]['context']['duration_ms']);
    }

    public function testListenerReceivesFailureEventViaDispatcher(): void
    {
        $command = $this->createCommand();
        $exception = new \RuntimeException('Test error');
        $event = new IntegrationFailureEvent(
            'App\\Command',
            'slack',
            $command,
            $exception,
            145.5,
            []
        );

        $this->dispatcher->dispatch($event);

        $this->assertCount(1, $this->loggedMessages);
        $this->assertSame(LogLevel::ERROR, $this->loggedMessages[0]['level']);
        $this->assertStringContainsString('slack', $this->loggedMessages[0]['message']);
        $this->assertSame('failure', $this->loggedMessages[0]['context']['status']);
        $this->assertSame('Test error', $this->loggedMessages[0]['context']['error']);
    }

    public function testMultipleEventsAreLogged(): void
    {
        $command = $this->createCommand();

        // Request
        $requestEvent = new IntegrationRequestEvent(
            'App\\Command',
            'slack',
            $command,
            []
        );
        $this->dispatcher->dispatch($requestEvent);

        // Success
        $successEvent = new IntegrationSuccessEvent(
            'App\\Command',
            'slack',
            $command,
            'success',
            100.0,
            []
        );
        $this->dispatcher->dispatch($successEvent);

        $this->assertCount(2, $this->loggedMessages);
        $this->assertSame('request', $this->loggedMessages[0]['context']['status']);
        $this->assertSame('success', $this->loggedMessages[1]['context']['status']);
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

