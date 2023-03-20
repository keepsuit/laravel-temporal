<?php

declare(strict_types=1);

namespace Keepsuit\LaravelTemporal\Contracts;

interface TemporalInterface
{
    public function newActivity(): object;
    public function newLocalActivity(): object;
    public function newWorkflow(): object;
    public function newChildWorkflow(): object;
}
