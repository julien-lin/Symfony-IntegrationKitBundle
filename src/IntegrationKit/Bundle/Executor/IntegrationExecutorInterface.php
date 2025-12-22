<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Executor;

use IntegrationKit\Bundle\ApiResult;
use IntegrationKit\Bundle\IntegrationCommand;

/**
 * Interface pour l'exécution des commandes d'intégration.
 *
 * L'executor gère l'exécution avec instrumentation (événements, logs, métriques).
 */
interface IntegrationExecutorInterface
{
    /**
     * Exécute une commande d'intégration.
     *
     * @param IntegrationCommand $command Commande typée
     * @return mixed Retour de la méthode d'intégration (peut être void, valeur, ou exception)
     * @throws \Throwable Si l'exécution échoue
     */
    public function execute(IntegrationCommand $command): mixed;

    /**
     * Exécute avec instrumentation (retourne ApiResult pour logs/métriques).
     *
     * À utiliser pour batch, async, ou monitoring.
     *
     * @param IntegrationCommand $command Commande typée
     * @return ApiResult Résultat standardisé
     */
    public function executeWithResult(IntegrationCommand $command): ApiResult;
}

