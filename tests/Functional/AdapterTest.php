<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Functional;

use G41797\Queue\Valkey\Adapter;
use G41797\Queue\Valkey\Configuration as BrokerConfiguration;
use G41797\Queue\Valkey\NullLoop;
use G41797\Queue\Valkey\Support\CheckMessageHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Yiisoft\Queue\Adapter\AdapterInterface;
use Yiisoft\Queue\Message\IdEnvelope;
use Yiisoft\Queue\Message\Message;
use Yiisoft\Queue\Message\MessageInterface;

class AdapterTest extends FunctionalTestCase
{

    private ?LoggerInterface $logger = null;
    private function getLogger(): LoggerInterface
    {
        if ($this->logger == null) {
            $this->logger = new NullLogger();
        }
        return $this->logger;
    }

    private ?CheckMessageHandler $handler = null;

    protected function getCallback(): callable
    {
        return [$this->getHandler(), 'handle'];
    }

    protected function getHandler(): CheckMessageHandler
    {
        if ($this->handler == null) {
            $this->handler = new CheckMessageHandler();
        }

        return $this->handler;
    }

    private ?NullLoop $loop = null;
    protected function getLoop(): NullLoop
    {
        if ($this->loop == null) {
            $this->loop = new NullLoop();
        }

        return $this->loop;
    }

    protected function createSubmitter() : AdapterInterface {
        $logger = $this->getLogger();
        return Adapter::default(logger: $logger);
    }

    protected function createWorker() : AdapterInterface {
        $logger = $this->getLogger();
        $loop = $this->getLoop();
        return Adapter::default(logger: $logger, loop: $loop);
    }

    static function getJob(): MessageInterface {
        return new Message("handler", data: 'data',metadata: []);
    }
    public function testSubmitterWorker(): void
    {
        $job = self::getJob();

        $submitter = $this->createSubmitter();

        $count = 10;

        $submitted = $this->submit($submitter, $job, $count);

        $this->assertCount($count, $submitted);

        $worker = $this->createWorker();

        $this->process($worker, $job, $submitted);

        return;
    }

    protected function submit(AdapterInterface $submitter, MessageInterface $job, int $count = 1000): array
    {
        $ids = [];

        for ($i= 0; $i < $count; $i++) {

            $submitted = $submitter->push($job);

            $this->assertTrue($submitted instanceof IdEnvelope);
            $this->assertArrayHasKey(IdEnvelope::MESSAGE_ID_KEY, $submitted->getMetadata());
            $id = $submitted->getMetadata()[IdEnvelope::MESSAGE_ID_KEY];
            $this->assertIsString($id);

            $ids[] = $id;
        }

        return $ids;
    }

    protected function process(AdapterInterface $worker, MessageInterface $expectedJob, array $ids): void
    {
        $this->getLoop()->update(count($ids));
        //$this->getLoop()->update(1000);
        $this->getHandler()->update($expectedJob);

        $worker->subscribe($this->getCallback());

        $this->assertEquals(count($ids), $this->getHandler()->processed());

        return;
    }


}
