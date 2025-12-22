<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit;

use IntegrationKit\Bundle\IntegrationInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour l'interface IntegrationInterface.
 */
final class IntegrationInterfaceTest extends TestCase
{
    public function testIntegrationMustImplementGetName(): void
    {
        $integration = new class implements IntegrationInterface {
            public function getName(): string
            {
                return 'test';
            }
        };

        $this->assertSame('test', $integration->getName());
    }

    public function testGetNameReturnsNonEmptyString(): void
    {
        $integration = new class implements IntegrationInterface {
            public function getName(): string
            {
                return 'slack';
            }
        };

        $name = $integration->getName();

        $this->assertIsString($name);
        $this->assertNotEmpty($name);
    }

    public function testTwoIntegrationsCanHaveSameName(): void
    {
        $integration1 = new class implements IntegrationInterface {
            public function getName(): string
            {
                return 'slack';
            }
        };

        $integration2 = new class implements IntegrationInterface {
            public function getName(): string
            {
                return 'slack';
            }
        };

        // La validation d'unicité se fait ailleurs (compiler pass)
        $this->assertSame('slack', $integration1->getName());
        $this->assertSame('slack', $integration2->getName());
    }
}

