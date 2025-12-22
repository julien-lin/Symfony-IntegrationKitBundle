<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Exception;

use IntegrationKit\Bundle\Exception\IntegrationNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour l'exception IntegrationNotFoundException.
 */
final class IntegrationNotFoundExceptionTest extends TestCase
{
    public function testExceptionContainsIntegrationName(): void
    {
        $exception = new IntegrationNotFoundException('slack');

        $this->assertSame('slack', $exception->getIntegrationName());
    }

    public function testExceptionContainsAvailableIntegrations(): void
    {
        $available = ['slack', 'stripe', 'crm'];
        $exception = new IntegrationNotFoundException('unknown', $available);

        $this->assertSame($available, $exception->getAvailableIntegrations());
    }

    public function testExceptionMessageIsInformative(): void
    {
        $exception = new IntegrationNotFoundException('unknown');

        $message = $exception->getMessage();

        $this->assertStringContainsString('unknown', $message);
        $this->assertNotEmpty($message);
    }

    public function testExceptionMessageContainsAvailableIntegrations(): void
    {
        $available = ['slack', 'stripe'];
        $exception = new IntegrationNotFoundException('unknown', $available);

        $message = $exception->getMessage();

        $this->assertStringContainsString('unknown', $message);
        $this->assertStringContainsString('slack', $message);
        $this->assertStringContainsString('stripe', $message);
    }

    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new IntegrationNotFoundException('test');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testExceptionWithEmptyAvailableList(): void
    {
        $exception = new IntegrationNotFoundException('unknown', []);

        $this->assertSame([], $exception->getAvailableIntegrations());
        $this->assertStringContainsString('unknown', $exception->getMessage());
    }
}

