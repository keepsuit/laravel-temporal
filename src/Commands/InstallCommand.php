<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;

class InstallCommand extends Command
{
    use Concerns\RoadrunnerDependencies;

    protected $signature = 'temporal:install';

    protected $description = 'Install temporal worker dependencies';

    public function handle(
        RoadRunnerBinaryHelper $roadRunnerBinaryHelper
    ): ?int {
        $this->ensureBinariesAreIgnored();

        $this->ensureRoadRunnerBinaryIsInstalled($roadRunnerBinaryHelper);

        return Command::SUCCESS;
    }

    protected function ensureBinariesAreIgnored(): void
    {
        if (File::exists(base_path('.gitignore'))) {
            collect(['rr', '.rr.yaml'])
                ->each(function (string $file): void {
                    $contents = File::get(base_path('.gitignore'));
                    if (! Str::contains($contents, $file.PHP_EOL)) {
                        File::append(
                            base_path('.gitignore'),
                            $file.PHP_EOL
                        );
                    }
                });
        }
    }
}
