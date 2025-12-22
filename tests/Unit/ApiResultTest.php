<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit;

use IntegrationKit\Bundle\ApiResult;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour la classe ApiResult.
 */
final class ApiResultTest extends TestCase
{
    public function testSuccessCreatesResultWithSuccessStatus(): void
    {
        $result = ApiResult::success(['key' => 'value']);

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
    }

    public function testFailureCreatesResultWithFailureStatus(): void
    {
        $result = ApiResult::failure('Error message');

        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
    }

    public function testIsSuccessReturnsTrueForSuccess(): void
    {
        $result = ApiResult::success();

        $this->assertTrue($result->isSuccess());
    }

    public function testIsSuccessReturnsFalseForFailure(): void
    {
        $result = ApiResult::failure('Error');

        $this->assertFalse($result->isSuccess());
    }

    public function testIsFailureReturnsTrueForFailure(): void
    {
        $result = ApiResult::failure('Error');

        $this->assertTrue($result->isFailure());
    }

    public function testIsFailureReturnsFalseForSuccess(): void
    {
        $result = ApiResult::success();

        $this->assertFalse($result->isFailure());
    }

    public function testGetDataReturnsDataForSuccess(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        $result = ApiResult::success($data);

        $this->assertSame($data, $result->getData());
    }

    public function testGetDataReturnsNullForFailure(): void
    {
        $result = ApiResult::failure('Error');

        $this->assertNull($result->getData());
    }

    public function testGetErrorReturnsErrorForFailure(): void
    {
        $error = 'Something went wrong';
        $result = ApiResult::failure($error);

        $this->assertSame($error, $result->getError());
    }

    public function testGetErrorReturnsNullForSuccess(): void
    {
        $result = ApiResult::success();

        $this->assertNull($result->getError());
    }

    public function testGetExceptionReturnsExceptionIfProvided(): void
    {
        $exception = new \RuntimeException('Previous error');
        $result = ApiResult::failure('Error message', $exception);

        $this->assertSame($exception, $result->getException());
    }

    public function testGetExceptionReturnsNullIfNotProvided(): void
    {
        $result = ApiResult::failure('Error message');

        $this->assertNull($result->getException());
    }

    public function testGetExceptionReturnsNullForSuccess(): void
    {
        $result = ApiResult::success();

        $this->assertNull($result->getException());
    }

    public function testGetMetadataReturnsMetadata(): void
    {
        $metadata = ['trace_id' => 'abc123', 'user_id' => 42];
        $result = ApiResult::success(['data'], $metadata);

        $this->assertSame($metadata, $result->getMetadata());
    }

    public function testGetMetadataReturnsEmptyArrayByDefault(): void
    {
        $result = ApiResult::success();

        $this->assertSame([], $result->getMetadata());
    }

    public function testGetMetadataForFailure(): void
    {
        $metadata = ['retry_count' => 3];
        $result = ApiResult::failure('Error', null, $metadata);

        $this->assertSame($metadata, $result->getMetadata());
    }

    public function testResultIsImmutable(): void
    {
        $result = ApiResult::success(['data']);

        // Vérifier qu'il n'y a pas de setters
        $reflection = new \ReflectionClass($result);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if (str_starts_with($method->getName(), 'set')) {
                $this->fail('ApiResult should not have setters (immutability)');
            }
        }

        $this->assertTrue(true); // Si on arrive ici, pas de setters
    }

    public function testSuccessWithNullData(): void
    {
        $result = ApiResult::success(null);

        $this->assertTrue($result->isSuccess());
        $this->assertNull($result->getData());
    }

    public function testSuccessWithObjectData(): void
    {
        $object = new \stdClass();
        $object->property = 'value';
        $result = ApiResult::success($object);

        $this->assertSame($object, $result->getData());
    }

    public function testFailureWithExceptionAndMetadata(): void
    {
        $exception = new \RuntimeException('Error');
        $metadata = ['key' => 'value'];
        $result = ApiResult::failure('Error message', $exception, $metadata);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Error message', $result->getError());
        $this->assertSame($exception, $result->getException());
        $this->assertSame($metadata, $result->getMetadata());
    }
}

