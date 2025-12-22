# IntegrationKitBundle

**Standardiser la consommation d'APIs externes dans Symfony, sans réinventer la roue.**

## Proposition de valeur

Ce bundle fournit une couche d'abstraction minimale pour standardiser l'appel d'APIs externes (Stripe, Slack, CRM, webhooks...) dans du code métier Symfony. Il s'appuie sur `HttpClient`, `EventDispatcher` et `Messenger` sans les remplacer, et garantit que chaque intégration est testable, traçable et normalisée.

**Points clés :**
- Le bundle n'impose aucun modèle métier et n'encapsule pas HttpClient
- Chaque intégration utilise HttpClient directement et expose des méthodes métier typées
- L'exécution passe par des Command objets typés (pas de strings magiques, pas de réflexion dynamique)

## Public cible

**Pour qui :**
- Équipes qui multiplient les intégrations externes (3+ APIs différentes)
- Projets où la traçabilité des appels API est critique (logs, métriques, erreurs)
- Codebases où l'on veut éviter la duplication de code HTTP (retries, timeouts, authentification)
- Équipes qui veulent tester les intégrations sans appels HTTP réels

**Pour qui PAS :**
- Projets avec une seule intégration externe (overhead inutile)
- Équipes qui préfèrent le contrôle total sur chaque appel HTTP
- Projets où chaque API nécessite une logique HTTP très spécifique
- Applications qui n'ont pas besoin de traçabilité standardisée

## Exemple d'usage (30 lignes)

```php
// 1. Définir une Command typée (contrat d'exécution)
final class SlackNotifyCommand implements IntegrationCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $name
    ) {}
    
    public function integrationName(): string
    {
        return 'slack';
    }
}

// 2. Définir un Handler explicite (mapping typé, pas de réflexion)
final class SlackNotifyHandler implements IntegrationHandlerInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $webhookUrl
    ) {}
    
    public function supports(): string
    {
        return SlackNotifyCommand::class;
    }
    
    public function handle(IntegrationCommand $command): void
    {
        assert($command instanceof SlackNotifyCommand);
        
        $response = $this->httpClient->request('POST', $this->webhookUrl, [
            'json' => ['text' => "User {$command->email} ({$command->name}) registered"]
        ]);
        
        if (200 !== $response->getStatusCode()) {
            throw new IntegrationException('slack', 'Notification failed');
        }
    }
}

// 3. L'utiliser dans le code métier (typage fort, auto-complétion)
class UserService
{
    public function __construct(
        private IntegrationExecutor $executor
    ) {}
    
    public function notifyUserRegistered(User $user): void
    {
        // Command typée = sécurité à la compilation, refactoring safe
        $this->executor->execute(new SlackNotifyCommand(
            $user->getEmail(),
            $user->getName()
        ));
    }
}
```

## Installation

```bash
composer require integration-kit/bundle
```

## Configuration

Le bundle fonctionne sans configuration YAML. Il suffit d'enregistrer vos intégrations et handlers :

```yaml
# config/services.yaml
services:
    App\Integration\SlackIntegration:
        tags:
            - { name: 'integration_kit.integration', name: 'slack' }
    
    App\Integration\SlackNotifyHandler:
        arguments:
            $webhookUrl: '%env(SLACK_WEBHOOK_URL)%'
        tags:
            - { name: 'integration_kit.handler', command: 'App\Integration\SlackNotifyCommand' }
```

## Documentation complète

Voir [EXEMPLE_USAGE.md](EXEMPLE_USAGE.md) pour un guide complet avec exemples.

## Licence

MIT

