# Yii3 Queue Adapter for Amazon Simple Queue Service (AWS SQS) 


[![tests](https://github.com/g41797/queue-sqs/actions/workflows/tests.yml/badge.svg)](https://github.com/g41797/queue-sqs/actions/workflows/tests.yml)

## Description

Yii3 Queue Adapter for [**AWS SQS**](https://aws.amazon.com/documentation-overview/sqs/) is new adapter in [Yii3 Queue Adapters family.](https://github.com/yiisoft/queue/blob/master/docs/guide/en/adapter-list.md)

Implementation of adapter is based on [enqueue/sqs](https://github.com/php-enqueue/sqs) library.

## Requirements

- PHP 8.2 or higher.

## Installation

The package could be installed with composer:

```shell
composer require g41797/queue-sqs
```

## General usage

- As part of [Yii3 Queue Framework](https://github.com/yiisoft/queue/blob/master/docs/guide/en/README.md)
- Stand-alone


## Configuration

Default configuration:
```php
[
     'key' => null,                 // AWS credentials. If no credentials are provided, the SDK will attempt to load them from the environment.
     'secret' => null,              // AWS credentials. If no credentials are provided, the SDK will attempt to load them from the environment.
     'token' => null,               // AWS credentials. If no credentials are provided, the SDK will attempt to load them from the environment.
     'region' => us-east-1,         // (string, required) Region to connect to. See http://docs.aws.amazon.com/general/latest/gr/rande.html for a list of available regions.
     'retries' => 3,                // (int, default=int(3)) Configures the maximum number of allowed retries for a client (pass 0 to disable retries).
     'version' => 'latest',         // (string, required) The version of the webservice to utilize
     'profile' => null,             // (string, default=null) The name of an AWS profile to used, if provided the SDK will attempt to read associated credentials from the ~/.aws/credentials file.
     'queue_owner_aws_account_id'   // The AWS account ID of the account that created the queue.
]
```
## Yandex Message Queue

According to [Yandex blog](https://habr.com/ru/companies/yandex/articles/455642/):
> "...we decided not to invent a unique interface for Yandex Message Queue,     
>  but implement support for the AWS SQS API, and very carefully."

It means that _queue-sqs_ supports [Yandex Message Queue](https://yandex.cloud/en/services/message-queue),
but this use case was not tested.

## LocalStack usage

[LocalStack](https://www.localstack.cloud/) allows "...develop and test your AWS applications locally to reduce development time...".

Development and testing of queue-sqs were done using LocalStack.

### Credentials

LocalStack does not require AWS credentials.
Functional tests use following credentials:
```php
[
    'key' => 'anyKey',
    'secret' => 'noSecrets',
]
```
### Endpoint

[AWS SDK for PHP](https://github.com/aws/aws-sdk-php) automatically builds required SQS endpoint.

Under LocalStack "hard-coded" endpoint is saved in phpunit configuration file:
```xml
    <php>
        <ini name="error_reporting" value="-1"/>
        <env name="ENDPOINT" value="http://localhost.localstack.cloud:4566" force="true" />
    </php>
```

_queue-sqs_ checks existence of **ENDPOINT**  and initiates AWS library accordingly.

### Auth Token

[Auth Token](https://app.localstack.cloud/workspace/auth-token) is used for authentication and to retrieve your LocalStack license.

You can see example in [startup script](https://github.com/g41797/queue-sqs/blob/master/docker/start.sh)
```shell
export LOCALSTACK_AUTH_TOKEN="ls-KESiVaLi-4697-7857-MEna-ziqIXeSaf962"
```
Replace this token with your one.


## Limitations

### Job Status

  [Job Status](https://github.com/yiisoft/queue/blob/master/docs/guide/en/usage.md#job-status)
```php
// Push a job into the queue and get a message ID.
$id = $queue->push(new SomeJob());

// Get job status.
$status = $queue->status($id);
```
is not supported.

### SQS limitations

A lot of information you can find in [Amazon SQS FAQs](https://www.amazonaws.cn/en/sqs/faqs/)

#### Channel Name

- Length limited to 75 chars
- Contains only alphanumeric characters, hyphens (-), and underscores (_)

#### Cross region/account communication

Cross region/account communications are not supported.

#### Long polling

Worker uses polling timeout for retrieving messages(jobs) from SQS.
Because customer pays for every receive, it's recommended to use long value
for this timeout.
Maximal value is 20 sec, this value is used also for zero timeout.

More information see [Amazon SQS short and long polling](https://docs.amazonaws.cn/en_us/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-short-and-long-polling.html)

## License

Yii3 Queue Adapter for AWS SQS is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.
