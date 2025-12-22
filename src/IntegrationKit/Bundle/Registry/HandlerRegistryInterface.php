<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Registry;

use IntegrationKit\Bundle\IntegrationHandlerInterface;

/**
 * Interface pour le registry des handlers.
 *
 * Le registry construit le mapping explicite [command_class => handler_service]
 * à la compilation. Si une commande n'a pas de handler, une exception est levée.
 */
interface HandlerRegistryInterface
{
    /**
     * Retourne le handler pour une classe de commande.
     *
     * @param string $commandClass FQCN de la commande
     * @return IntegrationHandlerInterface
     * @throws \RuntimeException Si aucun handler n'est trouvé
     */
    public function getHandlerFor(string $commandClass): IntegrationHandlerInterface;

    /**
     * Vérifie si un handler existe pour une classe de commande.
     *
     * @param string $commandClass FQCN de la commande
     * @return bool
     */
    public function hasHandlerFor(string $commandClass): bool;
}

