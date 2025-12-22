<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Executor;

use IntegrationKit\Bundle\ApiResult;
use IntegrationKit\Bundle\Event\IntegrationFailureEvent;
use IntegrationKit\Bundle\Event\IntegrationRequestEvent;
use IntegrationKit\Bundle\Event\IntegrationSuccessEvent;
use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\Registry\HandlerRegistryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Executes integration commands with instrumentation.
 *
 * Responsibilities:
 * - Handler resolution via HandlerRegistry
 * - Event dispatching (before/after)
 * - Execution duration measurement
 * - Exception catching and wrapping if necessary
 * - ApiResult return for instrumentation (if requested)
 */
final class IntegrationExecutor implements IntegrationExecutorInterface
{
    public function __construct(
        private readonly HandlerRegistryInterface $handlerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(IntegrationCommand $command): mixed
    {
        $handler = $this->handlerRegistry->getHandlerFor($command::class);
        $integrationName = $command->integrationName();

        // Dispatch before call
        $requestEvent = new IntegrationRequestEvent(
            $command::class,
            $integrationName,
            $command,
            []
        );
        $this->eventDispatcher->dispatch($requestEvent);

        // Duration measurement
        $startTime = microtime(true);

        try {
            // Actual handler call
            $result = $handler->handle($command);

            $duration = (microtime(true) - $startTime) * 1000; // Convert to ms

            // Dispatch after success
            $successEvent = new IntegrationSuccessEvent(
                $command::class,
                $integrationName,
                $command,
                $result,
                $duration,
                $requestEvent->getMetadata()
            );
            $this->eventDispatcher->dispatch($successEvent);

            return $result;
        } catch (\Throwable $exception) {
            $duration = (microtime(true) - $startTime) * 1000; // Convert to ms

            // Dispatch after failure
            $failureEvent = new IntegrationFailureEvent(
                $command::class,
                $integrationName,
                $command,
                $exception,
                $duration,
                $requestEvent->getMetadata()
            );
            $this->eventDispatcher->dispatch($failureEvent);

            throw $exception;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function executeWithResult(IntegrationCommand $command): ApiResult
    {
        $handler = $this->handlerRegistry->getHandlerFor($command::class);
        $integrationName = $command->integrationName();

        // Dispatch before call
        $requestEvent = new IntegrationRequestEvent(
            $command::class,
            $integrationName,
            $command,
            []
        );
        $this->eventDispatcher->dispatch($requestEvent);

        // Duration measurement
        $startTime = microtime(true);

        try {
            // Actual handler call
            $result = $handler->handle($command);

            $duration = (microtime(true) - $startTime) * 1000; // Convert to ms

            // Dispatch after success
            $successEvent = new IntegrationSuccessEvent(
                $command::class,
                $integrationName,
                $command,
                $result,
                $duration,
                $requestEvent->getMetadata()
            );
            $this->eventDispatcher->dispatch($successEvent);

            // Return ApiResult with metadata including duration
            $metadata = $requestEvent->getMetadata();
            $metadata['duration_ms'] = $duration;

            return ApiResult::success($result, $metadata);
        } catch (\Throwable $exception) {
            $duration = (microtime(true) - $startTime) * 1000; // Convert to ms

            // Dispatch after failure
            $failureEvent = new IntegrationFailureEvent(
                $command::class,
                $integrationName,
                $command,
                $exception,
                $duration,
                $requestEvent->getMetadata()
            );
            $this->eventDispatcher->dispatch($failureEvent);

            // Return ApiResult with metadata including duration
            $metadata = $requestEvent->getMetadata();
            $metadata['duration_ms'] = $duration;

            return ApiResult::failure(
                $exception->getMessage(),
                $exception,
                $metadata
            );
        }
    }
}

