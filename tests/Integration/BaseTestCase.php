<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Integration;

use Symfony\Component\Process\Process;
use G41797\Queue\Valkey\Support\FileHelper;
use G41797\Queue\Valkey\Functional\FunctionalTestCase;

abstract class BaseTestCase extends FunctionalTestCase
{
    /** @var Process[] */
    private array $processes = [];

    public function setUp(): void
    {
        parent::setUp();

        (new FileHelper())->clear();
    }

    protected function tearDown(): void
    {
        foreach ($this->processes as $process) {
            $process->stop();
        }
        $this->processes = [];

        (new FileHelper())->clear();

        parent::tearDown();
    }

    protected function queueListen(?string $queue = null): void
    {
        // TODO Fail test on subprocess error exit code
        $command = [PHP_BINARY, dirname(__DIR__) . '/listener', 'queue:listen'];
        if ($queue !== null) {
            $command[] = "--channel=$queue";
        }
        $process = new Process($command);
        $this->processes[] = $process;
        $process->start();
    }
}
