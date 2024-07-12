<?php

declare(strict_types=1);

namespace G41797\Queue\Sqs\Functional;

use G41797\Queue\Sqs\Broker;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Aws\Result;

class SnippetsTest extends FunctionalTestCase
{
    public function testListQueues(): void
    {
        $listQueues = self::listQueues();
        $this->assertNotNull($listQueues);
    }

    public function testPurgeQueues(): void
    {
    }

    static public function createClient(): ?SqsClient
    {
        return new SqsClient([
            'credentials' => false,
            'region' => 'us-east-1',
            'version' => 'latest',
            'use_path_style_endpoint' => true,
            'endpoint' => Broker::defaultEndpoint(),
        ]);
    }

    static public function listQueues(): ?Result
    {
        $client = self::createClient();

        if (null == $client)
        {
            return null;
        }

        try {
            $result = $client->listQueues();
            return $result;
        } catch (AwsException $e) {
            $emsg = $e->getMessage();
            return null;
        }
    }

    static public function purgeQueues(): bool
    {
        $client = self::createClient();

        if (null == $client)
        {
            return false;
        }

        try {
            $result = $client->listQueues();

            if (count($result) == 0)
            {
                return true;
            }

            foreach ($result->get('QueueUrls') as $queueUrl) {
                $client->purgeQueue(['QueueUrl' => $queueUrl]);
            }
            return true;
        } catch (AwsException $e) {
            $emsg = $e->getMessage();
            return false;
        }
    }
}
