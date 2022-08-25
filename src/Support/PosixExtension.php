<?php

namespace Keepsuit\LaravelTemporal\Support;

class PosixExtension
{
    /**
     * Send a signal to a given process using the POSIX extension.
     */
    public function kill(int $processId, int $signal): bool
    {
        return posix_kill($processId, $signal);
    }
}
