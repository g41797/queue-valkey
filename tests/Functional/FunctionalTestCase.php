<?php

declare(strict_types=1);

namespace G41797\Queue\Sqs\Functional;

use PHPUnit\Framework\TestCase;

use Yiisoft\Queue\Message\Message;
use Yiisoft\Queue\Message\MessageInterface;

use G41797\Queue\Sqs\Adapter;
use G41797\Queue\Sqs\Broker;
use G41797\Queue\Sqs\Configuration;


abstract class FunctionalTestCase extends TestCase
{
    public function setUp(): void
    {
        $this->clean();

        parent::setUp();
    }
    public function tearDown(): void
    {
        $this->clean();

        parent::tearDown();
    }
    public function clean(): void
    {
        $this->assertTrue(SnippetsTest::purgeQueues());
    }
    static public function defaultJob(): MessageInterface
    {
        return new Message('jobhandler', 'jobdata', metadata: []);
    }
}
