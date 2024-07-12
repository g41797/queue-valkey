<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Ramsey\Uuid\Uuid;

use Yiisoft\Queue\Enum\JobStatus;
use Yiisoft\Queue\Message\IdEnvelope;
use Yiisoft\Queue\Message\JsonMessageSerializer;
use Yiisoft\Queue\Message\MessageInterface;

use Interop\Queue\Producer;

use EnQueue\Redis\RedisContext;
use EnQueue\Redis\RedisConsumer;
use EnQueue\Redis\RedisConnectionFactory;
use EnQueue\Redis\RedisDestination;

use G41797\Queue\Valkey\Configuration as BrokerConfiguration;
use G41797\Queue\Valkey\Exception\NotSupportedStatusMethodException;
use G41797\Queue\Valkey\Exception\NotConnectedValkeyException;


class Broker implements BrokerInterface
{
    public const SUBSCRIPTION_NAME = 'jobs';

    private string $queueName;

    private JsonMessageSerializer $serializer;

    public function __construct(
        private string                 $channelName = Adapter::DEFAULT_CHANNEL_NAME,
        public ?BrokerConfiguration    $configuration = null,
        public ?LoggerInterface        $logger = null
    ) {
        $this->serializer = new JsonMessageSerializer();

        $this->queueName = $this->channelName . '.fifo';

        if (null == $configuration) {
            $this->configuration = new BrokerConfiguration();
        }

        $endpoint = self::defaultEndpoint();

        if (isset($endpoint)) {
            $this->configuration->update(['endpoint' => $endpoint]);
        }

        if (null == $logger) {
            $this->logger = new NullLogger();
        }

        return;
    }

    static public function default(): Broker
    {
        return new Broker();
    }

    public function withChannel(string $channel): BrokerInterface
    {
        if ($channel == $this->channelName) {
            return $this;
        }

        return new self($channel, $this->configuration, $this->logger);
    }

    private ?Producer $producer = null;
    public function push(MessageInterface $job): ?IdEnvelope
    {
        $this->prepare();

        if ($this->producer == null) {
            $this->producer = $this->valkey->createProducer();
        }

        $env = $this->submit($job);

        if ($env == null)
        {
            $this->producer = null;
        }

        return $env;
    }

    private function submit(MessageInterface $job): ?IdEnvelope
    {
        try {
            $jobId      = Uuid::uuid7()->toString();
            $payload    = $this->serializer->serialize($job);

            $valkeyMsg     = $this->valkey->createMessage(body: $payload);

            $valkeyMsg->setMessageDeduplicationId($jobId);
            $valkeyMsg->setMessageId($jobId);
            $valkeyMsg->setMessageGroupId(Broker::SUBSCRIPTION_NAME);

            $this->producer->send($this->queue, $valkeyMsg);

            return new IdEnvelope($job, $jobId);
        }
        catch (\Throwable ) {
            return null;
        }
    }

    public function jobStatus(string $id): ?JobStatus
    {
        throw new NotSupportedStatusMethodException();
    }

    private ?ValkeyConsumer $receiver = null;

    public function pull(float $timeout): ?IdEnvelope
    {
        $this->prepare();

        if ($this->receiver == null)
        {
            $this->receiver = $this->valkey->createConsumer($this->queue);
        }

        try
        {
            $valkeyMsg = $this->receiver->receive((int)(ceil($timeout*1000.0)));

            if (null == $valkeyMsg) { return null;}

            $job    = $this->serializer->unserialize($valkeyMsg->getBody());
            $jid    = $valkeyMsg->getMessageId();

            $this->receiver->acknowledge($valkeyMsg);

            return new IdEnvelope($job, $jid);
        }
        catch (\Exception $exc) {
            $this->receiver = null;
            return null;
        }
    }
    public function clean(): int
    {
        $count = 0;

        while (true)
        {
            $recv = $this->pull(1.0);
            if ($recv == null)
            {
                break;
            }

            $count += 1;
        }

        return $count;
    }

    public function done(string $id): bool
    {
        return !empty($id);
    }

    public ?ValkeyContext      $valkey    = null;
    public ValkeyDestination   $queue;
    public string       $queueUrl;

    private function prepare(): void
    {
        try
        {
            $this->init();
            return;
        }
        catch (\Exception $exc) {
            throw new NotConnectedValkeyException();
        }
    }

    private function init(): void
    {
        if ($this->valkey !== null)
        {
            return;
        }

        $valkey = (new ValkeyConnectionFactory($this->configuration->raw()))->createContext();

        $this->queue = $valkey->createQueue($this->queueName);

        $this->queue->setFifoQueue(true);
        $this->queue->setReceiveMessageWaitTimeSeconds((int)20);
        $this->queue->setContentBasedDeduplication(true);

        $valkey->declareQueue($this->queue);

        $this->queueUrl = $valkey->getQueueUrl($this->queue); // throws exception for failure
        $this->valkey      = $valkey;

        return;
    }

    static public function defaultEndpoint(): string|null
    {
        return $_ENV['ENDPOINT'];
    }

}
