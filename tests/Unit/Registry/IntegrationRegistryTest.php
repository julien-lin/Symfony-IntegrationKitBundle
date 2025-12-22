<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Registry;

use IntegrationKit\Bundle\Exception\IntegrationNotFoundException;
use IntegrationKit\Bundle\IntegrationInterface;
use IntegrationKit\Bundle\Registry\IntegrationRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour IntegrationRegistry.
 */
final class IntegrationRegistryTest extends TestCase
{
    private IntegrationRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new IntegrationRegistry();
    }

    public function testHasReturnsTrueIfIntegrationExists(): void
    {
        $integration = $this->createIntegration('slack');
        $this->registry->register('slack', $integration);

        $this->assertTrue($this->registry->has('slack'));
    }

    public function testHasReturnsFalseIfIntegrationDoesNotExist(): void
    {
        $this->assertFalse($this->registry->has('unknown'));
    }

    public function testGetReturnsIntegrationIfExists(): void
    {
        $integration = $this->createIntegration('slack');
        $this->registry->register('slack', $integration);

        $result = $this->registry->get('slack');

        $this->assertSame($integration, $result);
    }

    public function testGetThrowsIntegrationNotFoundExceptionIfNotFound(): void
    {
        $this->expectException(IntegrationNotFoundException::class);
        $this->expectExceptionMessage('Integration "unknown" not found');

        $this->registry->get('unknown');
    }

    public function testGetThrowsExceptionWithAvailableIntegrations(): void
    {
        $this->registry->register('slack', $this->createIntegration('slack'));
        $this->registry->register('stripe', $this->createIntegration('stripe'));

        try {
            $this->registry->get('unknown');
            $this->fail('Expected IntegrationNotFoundException');
        } catch (IntegrationNotFoundException $e) {
            $this->assertSame(['slack', 'stripe'], $e->getAvailableIntegrations());
            $this->assertStringContainsString('slack', $e->getMessage());
            $this->assertStringContainsString('stripe', $e->getMessage());
        }
    }

    public function testAllReturnsAllIntegrations(): void
    {
        $slack = $this->createIntegration('slack');
        $stripe = $this->createIntegration('stripe');

        $this->registry->register('slack', $slack);
        $this->registry->register('stripe', $stripe);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertSame($slack, $all['slack']);
        $this->assertSame($stripe, $all['stripe']);
    }

    public function testAllReturnsEmptyArrayWhenNoIntegrations(): void
    {
        $this->assertSame([], $this->registry->all());
    }

    public function testRegisterOverwritesExistingIntegration(): void
    {
        $integration1 = $this->createIntegration('slack');
        $integration2 = $this->createIntegration('slack');

        $this->registry->register('slack', $integration1);
        $this->registry->register('slack', $integration2);

        $this->assertSame($integration2, $this->registry->get('slack'));
    }

    private function createIntegration(string $name): IntegrationInterface
    {
        return new class($name) implements IntegrationInterface {
            public function __construct(private readonly string $name) {}

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}

