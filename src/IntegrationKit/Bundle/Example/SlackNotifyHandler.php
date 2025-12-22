<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Example;

use IntegrationKit\Bundle\Exception\IntegrationException;
use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\IntegrationHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Example handler to send Slack notifications.
 *
 * This example shows how to:
 * - Implement IntegrationHandlerInterface
 * - Use HttpClient directly (not hidden)
 * - Handle HTTP errors
 * - Use typed commands
 */
final class SlackNotifyHandler implements IntegrationHandlerInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $webhookUrl
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function supports(): string
    {
        return SlackNotifyCommand::class;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(IntegrationCommand $command): mixed
    {
        if (!$command instanceof SlackNotifyCommand) {
            throw new \InvalidArgumentException(
                sprintf('Expected %s, got %s', SlackNotifyCommand::class, $command::class)
            );
        }

        $payload = [
            'text' => $command->text,
        ];

        // Add blocks if present
        if (!empty($command->blocks)) {
            $payload['blocks'] = $command->blocks;
        }

        try {
            $response = $this->httpClient->request('POST', $this->webhookUrl, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();

            // Slack returns 200 on success
            if ($statusCode !== 200) {
                throw new IntegrationException(
                    'slack',
                    sprintf('Slack webhook returned status code %d', $statusCode)
                );
            }
        } catch (\Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface $e) {
            throw new IntegrationException(
                'slack',
                sprintf('HTTP error: %s', $e->getMessage()),
                $e
            );
        } catch (\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface $e) {
            throw new IntegrationException(
                'slack',
                sprintf('Transport error: %s', $e->getMessage()),
                $e
            );
        }

        // Return null as this method does not return a business value
        return null;
    }
}

