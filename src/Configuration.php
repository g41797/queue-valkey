<?php

declare(strict_types=1);

namespace G41797\Queue\Sqs;

final class Configuration
{
    private array $config = [];

    public function __construct(
        array $config = []
    ) {
        $this->config = array_replace(self::default(), $config);
    }

    public function update(array $config): self
    {
        $this->config = array_replace($this->config, $config);
        return $this;
    }

    public function raw(): array
    {
        return $this->config;
    }

    static public function default(): array
    {
        return [
            'key' => null,
            'secret' => null,
            'token' => null,
            'region' => 'us-east-1',
            'retries' => 3,
            'version' => 'latest',
            'profile' => null,
            'queue_owner_aws_account_id' => null
        ];
    }
}
