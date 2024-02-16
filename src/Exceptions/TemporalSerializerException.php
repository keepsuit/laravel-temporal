<?php

namespace Keepsuit\LaravelTemporal\Exceptions;

use Exception;

class TemporalSerializerException extends Exception
{
    public static function targetClassDoesntExists(string $className): self
    {
        return new self("Cannot deserialize value, class `$className` does not exist");
    }

    public static function targetClassIsNotSerializable(string $className): self
    {
        return new self("Cannot deserialize value, class `$className` does not implement `TemporalSerializable` interface");
    }
}
