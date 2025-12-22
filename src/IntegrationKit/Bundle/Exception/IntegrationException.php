<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Exception;

use IntegrationKit\Bundle\ApiResult;

/**
 * Generic exception for integration errors.
 *
 * This exception is used to wrap errors that occur during integration execution.
 * It contains context (integration name, result if available) to facilitate debugging.
 *
 * @example
 * ```php
 * throw new IntegrationException('slack', 'Notification failed');
 * ```
 *
 * @example
 * ```php
 * $result = ApiResult::failure('HTTP 500');
 * throw new IntegrationException('slack', 'Request failed', $previousException, $result);
 * ```
 */
final class IntegrationException extends \RuntimeException
{
    /**
     * @param string $integrationName Name of the integration that failed
     * @param string $message Error message
     * @param \Throwable|null $previous Previous exception (optional)
     * @param ApiResult|null $result Call result if available (optional)
     */
    public function __construct(
        private readonly string $integrationName,
        string $message,
        ?\Throwable $previous = null,
        private readonly ?ApiResult $result = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    /**
     * Returns the name of the integration that failed.
     *
     * @return string
     */
    public function getIntegrationName(): string
    {
        return $this->integrationName;
    }

    /**
     * Returns the call result if available.
     *
     * @return ApiResult|null
     */
    public function getResult(): ?ApiResult
    {
        return $this->result;
    }
}

