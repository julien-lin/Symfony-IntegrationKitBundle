<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Integration\Example;

use IntegrationKit\Bundle\ApiResult;
use IntegrationKit\Bundle\Example\SlackIntegration;
use IntegrationKit\Bundle\Example\SlackNotifyCommand;
use IntegrationKit\Bundle\Example\SlackNotifyHandler;
use IntegrationKit\Bundle\Executor\IntegrationExecutor;
use IntegrationKit\Bundle\Executor\IntegrationExecutorInterface;
use IntegrationKit\Bundle\Registry\HandlerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Test d'intégration pour l'exemple Slack.
 *
 * Cet exemple montre comment utiliser le bundle avec un MockHttpClient
 * pour tester sans appels HTTP réels.
 */
final class SlackIntegrationExampleTest extends TestCase
{
    private IntegrationExecutorInterface $executor;
    private HandlerRegistry $handlerRegistry;
    private EventDispatcher $eventDispatcher;
    private MockHttpClient $httpClient;

    protected function setUp(): void
    {
        $this->handlerRegistry = new HandlerRegistry();
        $this->eventDispatcher = new EventDispatcher();
        $this->executor = new IntegrationExecutor($this->handlerRegistry, $this->eventDispatcher);
        $this->httpClient = new MockHttpClient();
    }

    public function testSlackIntegrationCanBeRegistered(): void
    {
        $integration = new SlackIntegration();
        $this->assertSame('slack', $integration->getName());
    }

    public function testSlackNotifyHandlerSupportsCommand(): void
    {
        $handler = new SlackNotifyHandler($this->httpClient, 'https://hooks.slack.com/test');
        $this->assertSame(SlackNotifyCommand::class, $handler->supports());
    }

    public function testSlackNotifyCommandCanBeExecuted(): void
    {
        // Configurer le mock HTTP pour retourner un succès
        $this->httpClient->setResponseFactory(function () {
            return new MockResponse('ok', ['http_code' => 200]);
        });

        $handler = new SlackNotifyHandler($this->httpClient, 'https://hooks.slack.com/test');
        $this->handlerRegistry->register(SlackNotifyCommand::class, $handler);

        $command = new SlackNotifyCommand('Test message');

        // L'exécution ne doit pas lever d'exception
        $result = $this->executor->execute($command);
        $this->assertNull($result); // Le handler retourne void
    }

    public function testSlackNotifyCommandWithInstrumentation(): void
    {
        // Configurer le mock HTTP pour retourner un succès
        $this->httpClient->setResponseFactory(function () {
            return new MockResponse('ok', ['http_code' => 200]);
        });

        $handler = new SlackNotifyHandler($this->httpClient, 'https://hooks.slack.com/test');
        $this->handlerRegistry->register(SlackNotifyCommand::class, $handler);

        $command = new SlackNotifyCommand('Test message with instrumentation');

        // Utiliser executeWithResult pour obtenir l'instrumentation
        $result = $this->executor->executeWithResult($command);

        $this->assertInstanceOf(ApiResult::class, $result);
        $this->assertTrue($result->isSuccess());
        $this->assertArrayHasKey('duration_ms', $result->getMetadata());
    }

    public function testSlackNotifyCommandHandlesHttpErrors(): void
    {
        // Configurer le mock HTTP pour retourner une erreur
        $this->httpClient->setResponseFactory(function () {
            return new MockResponse('error', ['http_code' => 500]);
        });

        $handler = new SlackNotifyHandler($this->httpClient, 'https://hooks.slack.com/test');
        $this->handlerRegistry->register(SlackNotifyCommand::class, $handler);

        $command = new SlackNotifyCommand('Test message');

        // L'exécution doit lever une exception
        $this->expectException(\IntegrationKit\Bundle\Exception\IntegrationException::class);
        $this->expectExceptionMessage('Slack webhook returned status code 500');

        $this->executor->execute($command);
    }

    public function testSlackNotifyCommandWithBlocks(): void
    {
        // Configurer le mock HTTP pour retourner un succès
        $this->httpClient->setResponseFactory(function () {
            return new MockResponse('ok', ['http_code' => 200]);
        });

        $handler = new SlackNotifyHandler($this->httpClient, 'https://hooks.slack.com/test');
        $this->handlerRegistry->register(SlackNotifyCommand::class, $handler);

        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => 'Hello from IntegrationKitBundle!',
                ],
            ],
        ];

        $command = new SlackNotifyCommand('Test message', $blocks);

        // L'exécution ne doit pas lever d'exception
        $result = $this->executor->execute($command);
        $this->assertNull($result);
    }
}

