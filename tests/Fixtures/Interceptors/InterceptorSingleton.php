<?php

namespace Keepsuit\LaravelTemporal\Tests\Fixtures\Interceptors;

class InterceptorSingleton
{
    private static ?InterceptorSingleton $instance = null;

    protected array $data = [];

    public static function getInstance(): InterceptorSingleton
    {
        if (self::$instance === null) {
            self::$instance = new InterceptorSingleton();
        }

        return self::$instance;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function all(): array
    {
        return $this->data;
    }
}
