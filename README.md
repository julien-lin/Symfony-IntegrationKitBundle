# IntegrationKitBundle

**Standardize external API consumption in Symfony, without reinventing the wheel.**

## Value Proposition

This bundle provides a minimal abstraction layer to standardize external API calls (Stripe, Slack, CRM, webhooks...) in Symfony business code. It relies on `HttpClient`, `EventDispatcher` and `Messenger` without replacing them, and ensures that each integration is testable, traceable and normalized.

**Key points:**
- The bundle does not impose any business model and does not encapsulate HttpClient
- Each integration uses HttpClient directly and exposes typed business methods
- Execution goes through typed Command objects (no magic strings, no dynamic reflection)

## Target Audience

**For:**
- Teams that multiply external integrations (3+ different APIs)
- Projects where API call traceability is critical (logs, metrics, errors)
- Codebases where you want to avoid HTTP code duplication (retries, timeouts, authentication)
- Teams that want to test integrations without real HTTP calls

**NOT for:**
- Projects with a single external integration (unnecessary overhead)
- Teams that prefer total control over each HTTP call
- Projects where each API requires very specific HTTP logic
- Applications that don't need standardized traceability

## Usage Example (30 lines)

```php
// 1. Define a typed Command (execution contract)
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

// 2. Define an explicit Handler (typed mapping, no reflection)
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

// 3. Use it in business code (strong typing, auto-completion)
class UserService
{
    public function __construct(
        private IntegrationExecutor $executor
    ) {}
    
    public function notifyUserRegistered(User $user): void
    {
        // Typed command = compile-time safety, refactoring safe
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

The bundle works without YAML configuration. Just register your integrations and handlers:

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

## Complete Documentation

See [README.fr.md](README.fr.md) for complete documentation in French, or [EXEMPLE_USAGE.md](EXEMPLE_USAGE.md) for a comprehensive usage guide with examples.

## License

MIT
