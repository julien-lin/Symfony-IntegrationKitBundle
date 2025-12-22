<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle;

/**
 * Standardized result for instrumentation (logs, metrics, batch, async).
 *
 * This class represents the result of an integration call. It is used
 * for instrumentation (batch, async, monitoring) and is NOT required
 * for simple usage (where exceptions are preferred).
 *
 * The class is immutable (readonly properties, no setters).
 *
 * @example
 * ```php
 * // Success
 * $result = ApiResult::success(['user_id' => 42], ['trace_id' => 'abc123']);
 * if ($result->isSuccess()) {
 *     $data = $result->getData();
 * }
 *
 * // Failure
 * $result = ApiResult::failure('HTTP 500', $exception, ['retry_count' => 3]);
 * if ($result->isFailure()) {
 *     $error = $result->getError();
 *     $exception = $result->getException();
 * }
 * ```
 */
final class ApiResult
{
    /**
     * @param bool $success Success/failure status
     * @param mixed $data Return data (for success only)
     * @param string|null $error Error message (for failure only)
     * @param \Throwable|null $exception Thrown exception (for failure, optional)
     * @param array<string, mixed> $metadata Metadata (trace_id, retry_count, etc.)
     */
    private function __construct(
        private readonly bool $success,
        private readonly mixed $data = null,
        private readonly ?string $error = null,
        private readonly ?\Throwable $exception = null,
        private readonly array $metadata = []
    ) {
    }

    /**
     * Creates a success result.
     *
     * @param mixed $data Return data (can be null, array, object, etc.)
     * @param array<string, mixed> $metadata Metadata (trace_id, user_id, etc.)
     * @return self
     */
    public static function success(mixed $data = null, array $metadata = []): self
    {
        return new self(true, $data, null, null, $metadata);
    }

    /**
     * Creates a failure result.
     *
     * @param string $error Error message
     * @param \Throwable|null $exception Thrown exception (optional)
     * @param array<string, mixed> $metadata Metadata (retry_count, etc.)
     * @return self
     */
    public static function failure(string $error, ?\Throwable $exception = null, array $metadata = []): self
    {
        return new self(false, null, $error, $exception, $metadata);
    }

    /**
     * Indicates if the result is a success.
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Indicates if the result is a failure.
     *
     * @return bool
     */
    public function isFailure(): bool
    {
        return !$this->success;
    }

    /**
     * Returns the return data (for success only).
     *
     * @return mixed null for failure, data for success
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Returns the error message (for failure only).
     *
     * @return string|null null for success, error message for failure
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Returns the thrown exception (for failure, if provided).
     *
     * @return \Throwable|null null if no exception, exception otherwise
     */
    public function getException(): ?\Throwable
    {
        return $this->exception;
    }

    /**
     * Returns the metadata.
     *
     * @return array<string, mixed> Metadata (trace_id, retry_count, etc.)
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}

