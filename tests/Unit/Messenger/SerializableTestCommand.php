<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Messenger;

use IntegrationKit\Bundle\IntegrationCommand;

/**
 * Commande de test sérialisable pour les tests Messenger.
 */
final class SerializableTestCommand implements IntegrationCommand
{
    public function integrationName(): string
    {
        return 'test';
    }
}

