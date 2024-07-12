<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey;

use Psr\Log\LoggerInterface;
use G41797\Queue\Valkey\Configuration as BrokerConfiguration;

class BrokerFactory implements BrokerFactoryInterface
{

    public function get(
                            string $channel = Adapter::DEFAULT_CHANNEL_NAME,
                            array $config = [],
                            ?LoggerInterface $logger = null
                        ): ?BrokerInterface {
        return new Broker(
            $channel,
            new BrokerConfiguration($config),
            $logger
        );
    }
}
