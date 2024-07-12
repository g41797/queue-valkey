<?php

declare(strict_types=1);

namespace G41797\Queue\Sqs;

use Psr\Log\LoggerInterface;

interface BrokerFactoryInterface
{
    public function get(
            string $channel = Adapter::DEFAULT_CHANNEL_NAME,
            array $config = [],
            ?LoggerInterface $logger = null
        ): ?BrokerInterface;
}
