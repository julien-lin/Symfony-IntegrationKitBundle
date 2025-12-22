<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit;

use IntegrationKit\Bundle\IntegrationKitBundle;
use PHPUnit\Framework\TestCase;

/**
 * Test pour valider l'autoloading des classes du bundle.
 */
final class AutoloadingTest extends TestCase
{
    public function testBundleClassCanBeLoaded(): void
    {
        $this->assertTrue(
            class_exists(IntegrationKitBundle::class),
            'La classe IntegrationKitBundle doit être autoloadable'
        );
    }

    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new IntegrationKitBundle();

        $this->assertInstanceOf(IntegrationKitBundle::class, $bundle);
    }
}

