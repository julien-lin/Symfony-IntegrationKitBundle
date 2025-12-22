<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Integration;

use IntegrationKit\Bundle\IntegrationKitBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Test d'intégration pour valider que le bundle peut être chargé par Symfony.
 */
final class IntegrationKitBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new IntegrationKitBundle();

        $this->assertInstanceOf(IntegrationKitBundle::class, $bundle);
    }

    public function testBundleName(): void
    {
        $bundle = new IntegrationKitBundle();

        $this->assertSame('IntegrationKitBundle', $bundle->getName());
    }
}

