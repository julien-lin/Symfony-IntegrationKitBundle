<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Registry;

use IntegrationKit\Bundle\IntegrationInterface;

/**
 * Interface pour le registry des intégrations.
 *
 * Le registry stocke et résout les intégrations enregistrées.
 * Il ne gère PAS l'exécution (responsabilité de IntegrationExecutor).
 */
interface IntegrationRegistryInterface
{
    /**
     * Retourne une intégration par son nom.
     *
     * @param string $name Nom de l'intégration
     * @return IntegrationInterface
     * @throws \IntegrationKit\Bundle\Exception\IntegrationNotFoundException Si l'intégration n'existe pas
     */
    public function get(string $name): IntegrationInterface;

    /**
     * Vérifie si une intégration existe.
     *
     * @param string $name Nom de l'intégration
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Retourne toutes les intégrations enregistrées.
     *
     * @return array<string, IntegrationInterface> ['name' => IntegrationInterface]
     */
    public function all(): array;
}

