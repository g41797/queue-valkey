<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Functional;

use PHPUnit\Framework\TestCase;

use Yiisoft\Queue\Message\Message;
use Yiisoft\Queue\Message\MessageInterface;


abstract class FunctionalTestCase extends TestCase
{
    public function setUp(): void
    {
        $this->clean();

        parent::setUp();
    }
    public function clean(): void
    {
        $this->assertTrue(CleanerTest::purgeQueue());
    }
    static public function defaultJob(): MessageInterface
    {
        return new Message('jobhandler', 'jobdata', metadata: []);
    }
}
