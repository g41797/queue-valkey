<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class NotConnectedValkeyException extends \RuntimeException implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Not connected to Valkey.';
    }

    public function getSolution(): ?string
    {
        return 'Check your Valkey configuration.';
    }
}

