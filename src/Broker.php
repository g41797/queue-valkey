<?php

declare(strict_types=1);

namespace G41797\Queue\Sqs;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Ramsey\Uuid\Uuid;

use Yiisoft\Queue\Enum\JobStatus;
use Yiisoft\Queue\Message\IdEnvelope;
use Yiisoft\Queue\Message\JsonMessageSerializer;
use Yiisoft\Queue\Message\MessageInterface;

use Interop\Queue\Producer;

use Enqueue\Sqs\SqsContext;
use Enqueue\Sqs\SqsConsumer;
use Enqueue\Sqs\SqsConnectionFactory;
use Enqueue\Sqs\SqsDestination;

use G41797\Queue\Sqs\Configuration as BrokerConfiguration;
use G41797\Queue\Sqs\Exception\NotSupportedStatusMethodException;
use G41797\Queue\Sqs\Exception\NotConnectedSqsException;


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
            $this->producer = $this->sqs->createProducer();
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

            $sqsMsg     = $this->sqs->createMessage(body: $payload);

            $sqsMsg->setMessageDeduplicationId($jobId);
            $sqsMsg->setMessageId($jobId);
            $sqsMsg->setMessageGroupId(Broker::SUBSCRIPTION_NAME);

            $this->producer->send($this->queue, $sqsMsg);

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

    private ?SqsConsumer $receiver = null;

    public function pull(float $timeout): ?IdEnvelope
    {
        $this->prepare();

        if ($this->receiver == null)
        {
            $this->receiver = $this->sqs->createConsumer($this->queue);
        }

        try
        {
            $sqsMsg = $this->receiver->receive((int)(ceil($timeout*1000.0)));

            if (null == $sqsMsg) { return null;}

            $job    = $this->serializer->unserialize($sqsMsg->getBody());
            $jid    = $sqsMsg->getMessageId();

            $this->receiver->acknowledge($sqsMsg);

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

    public ?SqsContext      $sqs    = null;
    public SqsDestination   $queue;
    public string       $queueUrl;

    private function prepare(): void
    {
        try
        {
            $this->init();
            return;
        }
        catch (\Exception $exc) {
            throw new NotConnectedSqsException();
        }
    }

    private function init(): void
    {
        if ($this->sqs !== null)
        {
            return;
        }

        $sqs = (new SqsConnectionFactory($this->configuration->raw()))->createContext();

        $this->queue = $sqs->createQueue($this->queueName);

        $this->queue->setFifoQueue(true);
        $this->queue->setReceiveMessageWaitTimeSeconds((int)20);
        $this->queue->setContentBasedDeduplication(true);

        $sqs->declareQueue($this->queue);

        $this->queueUrl = $sqs->getQueueUrl($this->queue); // throws exception for failure
        $this->sqs      = $sqs;

        return;
    }

    static public function defaultEndpoint(): string|null
    {
        return $_ENV['ENDPOINT'];
    }

}
