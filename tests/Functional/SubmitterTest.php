<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Functional;

use G41797\Queue\Valkey\Broker;

class SubmitterTest extends FunctionalTestCase
{
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
        return new Broker();
    }

}
