<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Functional;

use G41797\Queue\Valkey\Adapter;
use G41797\Queue\Valkey\Broker;
use G41797\Queue\Valkey\Configuration;

class SubmitterTest extends FunctionalTestCase
{
    public function testSetUp(): void
    {
        $this->assertTrue(true);
        return;
    }

    public function testSubmit(): void
    {
        $count = 10;

        $this->assertEquals($count, count($this->submitJobs($count)));

        $this->assertEquals($count, self::testBroker()->clean());
    }

    private function submitJobs(int $count): array
    {
        $submitted = [];
        $submitter = self::testBroker();

        for ($i = 0; $i < $count; $i++) {
            $job = self::defaultJob();
            $env = $submitter->push($job);
            if ($env == null) {
                break;
            }
            $submitted[] = $env;
        }
        return $submitted;
    }

    static public function testBroker(): Broker
    {
        return new Broker(channelName:'testQueue', configuration: self::testConfig());
    }


    static public function testConfig(): Configuration
    {
        return new Configuration(self::testConfigArray());
    }
    static public function testConfigArray(): array
    {
        return [
            'key' => 'anyKey',
            'secret' => 'noSecrets',
        ];
    }

}
