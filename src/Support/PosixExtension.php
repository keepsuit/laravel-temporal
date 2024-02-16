<?php

namespace Keepsuit\LaravelTemporal\Support;

use Safe\Exceptions\PosixException;

class PosixExtension
{
    /**
     * Send a signal to a given process using the POSIX extension.
     */
    public function kill(int $processId, int $signal): bool
    {
        try {
            \Safe\posix_kill($processId, $signal);

            return true;
        } catch (PosixException) {
            return false;
        }
    }
}
