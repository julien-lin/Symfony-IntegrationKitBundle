<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Exception;

use IntegrationKit\Bundle\ApiResult;
use IntegrationKit\Bundle\Exception\IntegrationException;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour l'exception IntegrationException.
 */
final class IntegrationExceptionTest extends TestCase
{
    public function testExceptionContainsIntegrationName(): void
    {
        $exception = new IntegrationException('slack', 'Error message');

        $this->assertSame('slack', $exception->getIntegrationName());
    }

    public function testExceptionContainsMessage(): void
    {
        $exception = new IntegrationException('slack', 'Error message');

        $this->assertSame('Error message', $exception->getMessage());
    }

    public function testExceptionCanContainApiResult(): void
    {
        $result = ApiResult::failure('Test error');
        $exception = new IntegrationException('slack', 'Error message', null, $result);

        $this->assertSame($result, $exception->getResult());
    }

    public function testExceptionCanWrapPreviousException(): void
    {
        $previous = new \RuntimeException('Previous error');
        $exception = new IntegrationException('slack', 'Error message', $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new IntegrationException('test', 'Error');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithoutResult(): void
    {
        $exception = new IntegrationException('slack', 'Error message');

        $this->assertNull($exception->getResult());
    }

    public function testExceptionWithResultAndPrevious(): void
    {
        $previous = new \RuntimeException('Previous error');
        $result = ApiResult::failure('Test error');
        $exception = new IntegrationException('slack', 'Error message', $previous, $result);

        $this->assertSame('slack', $exception->getIntegrationName());
        $this->assertSame('Error message', $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($result, $exception->getResult());
    }
}

