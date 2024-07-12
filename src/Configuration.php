<?php

declare(strict_types=1);

namespace G41797\Queue\Valkey;

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
            'scheme' => 'redis',
            'scheme_extensions' => [],
            'host' => '127.0.0.1',
            'port' => 6379,
            'path' => null,
            'database' => null,
            'password' => null,
            'async' => false,
            'persistent' => false,
            'lazy' => false,
            'timeout' => 5.0,
            'read_write_timeout' => null,
            'predis_options' => null,
            'ssl' => null,
            'redelivery_delay' => 300,
        ];
    }
}
