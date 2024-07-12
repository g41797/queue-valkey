<?php

namespace G41797\Queue\Sqs\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class NotSupportedJobStatusMethodException  extends \RuntimeException implements FriendlyExceptionInterface
{
    public function getName(): string
    {
        return 'Not supported jobStatus API.';
    }

    public function getSolution(): ?string
    {
        return 'Do not use jobStatus API';
    }
}

