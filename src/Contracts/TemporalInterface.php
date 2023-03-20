<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Contracts;

interface TemporalInterface
{
    public function newActivity();
    public function newLocalActivity();
    public function newWorkflow();
    public function newChildWorkflow();
}
