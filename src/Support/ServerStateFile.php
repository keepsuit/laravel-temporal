<?php

namespace Keepsuit\LaravelTemporal\Support;

use RuntimeException;
use Safe\Exceptions\FilesystemException;

class ServerStateFile
{
    public function __construct(protected string $path)
    {
    }

    /**
     * Read the server state from the server state file.
     *
     * @return array{masterProcessId: mixed, state: mixed}
     */
    public function read(): array
    {
        $state = is_readable($this->path)
            ? \Safe\json_decode(\Safe\file_get_contents($this->path), true, 512, JSON_THROW_ON_ERROR)
            : [];

        return [
            'masterProcessId' => $state['masterProcessId'] ?? null,
            'state' => $state['state'] ?? [],
        ];
    }

    /**
     * Write the given process ID to the server state file.
     */
    public function writeProcessId(int $masterProcessId): void
    {
        if (! is_writable($this->path) && ! is_writable(dirname($this->path))) {
            throw new RuntimeException('Unable to write to process ID file.');
        }

        \Safe\file_put_contents($this->path, \Safe\json_encode(
            [...$this->read(), 'masterProcessId' => $masterProcessId],
            JSON_PRETTY_PRINT
        ));
    }

    /**
     * Write the given state array to the server state file.
     *
     * @param  array<string,string|int>  $newState
     */
    public function writeState(array $newState): void
    {
        if (! is_writable($this->path) && ! is_writable(dirname($this->path))) {
            throw new RuntimeException('Unable to write to process ID file.');
        }

        \Safe\file_put_contents($this->path, \Safe\json_encode(
            [...$this->read(), 'state' => $newState],
            JSON_PRETTY_PRINT
        ));
    }

    /**
     * Delete the process ID file.
     */
    public function delete(): bool
    {
        try {
            \Safe\unlink($this->path);

            return true;
        } catch (FilesystemException) {
            return false;
        }
    }

    /**
     * Get the path to the process ID file.
     */
    public function path(): string
    {
        return $this->path;
    }
}
