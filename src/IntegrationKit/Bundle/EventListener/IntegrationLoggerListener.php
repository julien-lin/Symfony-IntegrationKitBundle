<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\EventListener;

use IntegrationKit\Bundle\Event\IntegrationFailureEvent;
use IntegrationKit\Bundle\Event\IntegrationRequestEvent;
use IntegrationKit\Bundle\Event\IntegrationSuccessEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener to log integration calls in structured JSON.
 *
 * Logs IntegrationRequestEvent, IntegrationSuccessEvent,
 * and IntegrationFailureEvent in a structured JSON format to facilitate
 * ingestion into monitoring tools.
 *
 * Log format:
 * - timestamp (ISO 8601)
 * - integration_name
 * - command_class
 * - status (success/failure)
 * - duration_ms
 * - metadata
 * - error (for failure only)
 */
final class IntegrationLoggerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            IntegrationRequestEvent::class => 'onIntegrationRequest',
            IntegrationSuccessEvent::class => 'onIntegrationSuccess',
            IntegrationFailureEvent::class => 'onIntegrationFailure',
        ];
    }

    /**
     * Logs the request event.
     */
    public function onIntegrationRequest(IntegrationRequestEvent $event): void
    {
        $context = [
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'integration_name' => $event->getIntegrationName(),
            'command_class' => $event->getCommandClass(),
            'status' => 'request',
            'metadata' => $event->getMetadata(),
        ];

        $this->logger->info(
            sprintf(
                'Integration request: %s::%s',
                $event->getIntegrationName(),
                $event->getCommandClass()
            ),
            $context
        );
    }

    /**
     * Logs the success event.
     */
    public function onIntegrationSuccess(IntegrationSuccessEvent $event): void
    {
        $context = [
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'integration_name' => $event->getIntegrationName(),
            'command_class' => $event->getCommandClass(),
            'status' => 'success',
            'duration_ms' => $event->getDuration(),
            'metadata' => $event->getMetadata(),
        ];

        $this->logger->info(
            sprintf(
                'Integration success: %s::%s (duration: %.2f ms)',
                $event->getIntegrationName(),
                $event->getCommandClass(),
                $event->getDuration()
            ),
            $context
        );
    }

    /**
     * Logs the failure event.
     */
    public function onIntegrationFailure(IntegrationFailureEvent $event): void
    {
        $context = [
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'integration_name' => $event->getIntegrationName(),
            'command_class' => $event->getCommandClass(),
            'status' => 'failure',
            'duration_ms' => $event->getDuration(),
            'error' => $event->getException()->getMessage(),
            'exception_class' => $event->getException()::class,
            'metadata' => $event->getMetadata(),
        ];

        $this->logger->error(
            sprintf(
                'Integration failure: %s::%s - %s (duration: %.2f ms)',
                $event->getIntegrationName(),
                $event->getCommandClass(),
                $event->getException()->getMessage(),
                $event->getDuration()
            ),
            $context
        );
    }
}

