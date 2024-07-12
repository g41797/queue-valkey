# Yii3 Queue Adapter for Valkey NoSQL data store


[![tests](https://github.com/g41797/queue-valkey/actions/workflows/tests.yml/badge.svg)](https://github.com/g41797/queue-valkey/actions/workflows/tests.yml)

## Description

Yii3 Queue Adapter for [**Valkey NoSQL data store**](https://valkey.io/) is adapter in [Yii3 Queue Adapters family.](https://github.com/yiisoft/queue/blob/master/docs/guide/en/adapter-list.md)

Implementation of adapter is based on [enqueue/redis](https://github.com/php-enqueue) library.

## Requirements

- PHP 8.2 or higher.

## Installation

The package could be installed with composer:

```shell
composer require g41797/queue-valkey
```

## General usage

- As part of [Yii3 Queue Framework](https://github.com/yiisoft/queue/blob/master/docs/guide/en/README.md)
- Stand-alone


## Configuration

Default configuration:
```php
[
    'host' => '127.0.0.1',  // IP or hostname of the target server
    'port' => 6379,         // TCP/IP port of the target server
    'path' => null,         // Path of the UNIX domain socket file used when connecting to Valkey using UNIX domain sockets.
]
``` 
## Redis support

queue-valkey supports also [Redis](https://redis.io/):
- queue-valkey itself uses existing Redis client libraries 
- according to [Valkey](https://github.com/orgs/valkey-io/discussions/722#discussioncomment-9927734):
> "...Valkey 7.2 is fully compatible with Redis 7.2 
> and drop-in replacement is fully supported. 
> There is no need to change any of your application code."


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

## License

Yii3 Queue Adapter for Valkey is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.
