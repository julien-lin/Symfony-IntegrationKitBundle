<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle;

/**
 * Explicit handler for an integration command.
 *
 * Each handler explicitly declares which command it supports via `supports()`.
 * The executor resolves the handler via explicit mapping (no reflection,
 * no fragile convention). Type safety is guaranteed at compile time.
 *
 * @example
 * ```php
 * final class SlackNotifyHandler implements IntegrationHandlerInterface
 * {
 *     public function supports(): string
 *     {
 *         return SlackNotifyCommand::class;
 *     }
 *
 *     public function handle(IntegrationCommand $command): void
 *     {
 *         assert($command instanceof SlackNotifyCommand);
 *         // Slack sending logic
 *     }
 * }
 * ```
 */
interface IntegrationHandlerInterface
{
    /**
     * Returns the command class supported by this handler.
     *
     * @return class-string<IntegrationCommand> The FQCN of the supported command
     */
    public function supports(): string;

    /**
     * Executes the command.
     *
     * @param IntegrationCommand $command Typed command
     * @return mixed Execution return (can be void, value, or exception)
     */
    public function handle(IntegrationCommand $command): mixed;
}

