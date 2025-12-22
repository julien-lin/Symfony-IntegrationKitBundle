<?php

declare(strict_types=1);

namespace IntegrationKit\Bundle\Tests\Unit\Messenger;

use IntegrationKit\Bundle\ApiResult;
use IntegrationKit\Bundle\Executor\IntegrationExecutorInterface;
use IntegrationKit\Bundle\IntegrationCommand;
use IntegrationKit\Bundle\Messenger\IntegrationMessage;
use IntegrationKit\Bundle\Messenger\IntegrationMessageHandler;
use PHPUnit\Framework\TestCase;

/**
 * Tests pour IntegrationMessageHandler.
 */
final class IntegrationMessageHandlerTest extends TestCase
{
    private IntegrationExecutorInterface $executor;
    private IntegrationMessageHandler $handler;

    protected function setUp(): void
    {
        $this->executor = $this->createMock(IntegrationExecutorInterface::class);
        $this->handler = new IntegrationMessageHandler($this->executor);
    }

    public function testHandlerCallsExecutorWithCommand(): void
    {
        $command = new SerializableTestCommand();
        $message = new IntegrationMessage($command);

        $this->executor
            ->expects($this->once())
            ->method('executeWithResult')
            ->with($this->identicalTo($command))
            ->willReturn(ApiResult::success(['result' => 'ok']));

        ($this->handler)($message);
    }

    public function testHandlerHandlesSuccess(): void
    {
        $command = new SerializableTestCommand();
        $message = new IntegrationMessage($command);
        $result = ApiResult::success(['data' => 'test']);

        $this->executor
            ->expects($this->once())
            ->method('executeWithResult')
            ->willReturn($result);

        // Ne doit pas lever d'exception
        ($this->handler)($message);
    }

    public function testHandlerHandlesFailure(): void
    {
        $command = new SerializableTestCommand();
        $message = new IntegrationMessage($command);
        $exception = new \RuntimeException('Handler error');

        $this->executor
            ->expects($this->once())
            ->method('executeWithResult')
            ->willThrowException($exception);

        // Le handler doit propager l'exception pour que Messenger puisse la gérer (retry, etc.)
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler error');

        ($this->handler)($message);
    }

    public function testHandlerHandlesFailureResult(): void
    {
        $command = new SerializableTestCommand();
        $message = new IntegrationMessage($command);
        $result = ApiResult::failure('Error message', new \RuntimeException('Error'));

        $this->executor
            ->expects($this->once())
            ->method('executeWithResult')
            ->willReturn($result);

        // Un ApiResult en échec ne doit pas lever d'exception dans le handler
        // C'est à l'utilisateur de décider s'il veut lever une exception ou non
        ($this->handler)($message);
    }

    public function testHandlerIsInvokable(): void
    {
        $command = new SerializableTestCommand();
        $message = new IntegrationMessage($command);

        $this->executor
            ->expects($this->once())
            ->method('executeWithResult')
            ->willReturn(ApiResult::success([]));

        // Vérifier que le handler est invokable
        $this->assertTrue(is_callable($this->handler));
        ($this->handler)($message);
    }
}

