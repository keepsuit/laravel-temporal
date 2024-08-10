<?php

namespace Keepsuit\LaravelTemporal\Exceptions;

use Exception;

class TemporalSerializerException extends Exception
{
    public static function targetClassDoesntExists(string $className): self
    {
        return new self(sprintf('Cannot deserialize value, class `%s` does not exist', $className));
    }

    public static function targetClassIsNotSerializable(string $className): self
    {
        return new self(sprintf('Cannot deserialize value, class `%s` does not implement `TemporalSerializable` interface', $className));
    }
}
