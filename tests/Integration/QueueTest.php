<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Integration;

use InvalidArgumentException;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use G41797\Queue\Valkey\Adapter;
use G41797\Queue\Valkey\Support\FileHelper;
use G41797\Queue\Valkey\Support\SimpleMessageHandler;

use Yiisoft\Queue\Adapter\AdapterInterface;
use Yiisoft\Queue\Cli\LoopInterface;
use Yiisoft\Queue\Cli\SignalLoop;
use Yiisoft\Queue\Message\Message;
use Yiisoft\Queue\Queue;
use Yiisoft\Queue\Worker\WorkerInterface;
use Yiisoft\Queue\Middleware\CallableFactory;
use Yiisoft\Queue\Middleware\Push\MiddlewareFactoryPush;
use Yiisoft\Queue\Middleware\Push\PushMiddlewareDispatcher;

use Yiisoft\Test\Support\Container\SimpleContainer;

final class QueueTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->queueListen();
    }

    public function testMainFlow(): void
    {
        $fileHelper = new FileHelper();
        $adapter = Adapter::default(loop: new SignalLoop());
        $queue = $this->makeQueue($adapter);

        $queue->push(
            new Message('simple', 'testMainFlow')
        );

        sleep(2);
        $result = $fileHelper->get('testMainFlow');
        self::assertNotNull($result);
        $result = (int) $result;
    }

    private function makeQueue(AdapterInterface $adapter): Queue
    {
        $container = new SimpleContainer([]);
        $callableFactory = new CallableFactory($container);
        $pushMiddlewareDispatcher = new PushMiddlewareDispatcher(new MiddlewareFactoryPush($container, $callableFactory));
        $queue = new Queue(
            $this->createMock(WorkerInterface::class),
            $this->createMock(LoopInterface::class),
            $this->createMock(LoggerInterface::class),
            $pushMiddlewareDispatcher,
            $adapter,
        );
        return $queue;
    }
}
