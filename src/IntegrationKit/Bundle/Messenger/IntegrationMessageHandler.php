<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Messenger;

use IntegrationKit\Bundle\Executor\IntegrationExecutorInterface;

/**
 * Messenger handler to execute IntegrationMessage asynchronously.
 *
 * This handler is automatically registered by Symfony Messenger if Messenger is installed
 * via the #[AsMessageHandler] attribute (conditionally loaded in the Extension).
 *
 * It delegates execution to IntegrationExecutor to benefit from all infrastructure
 * (events, logging, instrumentation).
 *
 * Exceptions are propagated to allow Messenger to handle retries.
 */
final class IntegrationMessageHandler
{
    public function __construct(
        private readonly IntegrationExecutorInterface $executor
    ) {
    }

    /**
     * Executes the integration command contained in the message.
     *
     * @param IntegrationMessage $message Message containing the command to execute
     * @throws \Throwable Exceptions are propagated to allow Messenger retries
     */
    public function __invoke(IntegrationMessage $message): void
    {
        // Use executeWithResult to benefit from complete instrumentation
        // Exceptions are propagated so Messenger can handle retries
        $this->executor->executeWithResult($message->getCommand());
    }
}

