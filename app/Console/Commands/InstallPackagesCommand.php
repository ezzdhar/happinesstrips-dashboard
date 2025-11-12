<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class InstallPackagesCommand extends Command
{
    protected $signature = 'install:packages';

    protected $description = 'Install and publish required packages with progress tracking';

    public function handle()
    {
        $packages = [
            [
                'install' => 'composer require barryvdh/laravel-debugbar -W',
                'publish' => 'php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"',
                'description' => 'Installing Laravel Debugbar',
            ],
            [
                'install' => 'composer require laravel/pulse -W',
                'publish' => 'php artisan vendor:publish --provider="Laravel\Pulse\PulseServiceProvider"',
                'description' => 'Installing Laravel Pulse',
            ],
            [
                'install' => 'composer require livewire/livewire -W',
                'publish' => [
                    'php artisan livewire:publish --config',
                    'php artisan livewire:publish --assets',
                ],
                'description' => 'Installing Livewire',
            ],
            [
                'install' => 'composer require livewire/volt -W',
                'publish' => 'php artisan volt:install',
                'description' => 'Installing Livewire Volt',
            ],
            [
                'install' => 'composer require spatie/laravel-error-solutions -W',
                'publish' => 'php artisan vendor:publish --tag="error-solutions"',
                'description' => 'Installing Laravel Error Solutions',
            ],
            [
                'install' => 'composer require beyondcode/laravel-query-detector -W',
                'publish' => 'php artisan vendor:publish --provider="BeyondCode\QueryDetector\QueryDetectorServiceProvider"',
                'description' => 'Installing Laravel Query Detector',
            ],
            [
                'install' => 'composer require bensampo/laravel-enum -W',
                'publish' => 'php artisan vendor:publish --provider="BenSampo\Enum\EnumServiceProvider" --tag="translations"',
                'description' => 'Installing Laravel Enum',
            ],
            [
                'install' => 'composer require opcodesio/log-viewer',
                'publish' => [
                    'php artisan log-viewer:publish',
                    'php artisan vendor:publish --tag="log-viewer-config"',
                ],
                'description' => 'Installing Opcodes Log Viewer',
            ],
            [
                'install' => ' composer require spatie/laravel-permission -W',
                'publish' => 'php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"',
                'description' => 'Installing Laravel Permission',
            ],
            [
                'install' => 'composer require staudenmeir/eloquent-eager-limit -W',
                'publish' => '',
                'description' => 'Installing Eloquent Eager Limit',
            ],
            [
                'install' => 'composer require laravolt/avatar -W',
                'publish' => 'php artisan vendor:publish --provider="Laravolt\Avatar\ServiceProvider"',
                'description' => 'Installing Laravolt Avatar',
            ],
            [
                'install' => 'composer require outhebox/blade-flags -W',
                'publish' => [
                    'php artisan vendor:publish --tag=blade-flags-config',
                    'php artisan vendor:publish --tag=blade-flags --force',
                ],
                'description' => 'Installing Blade Flags',
            ],

            [
                'install' => 'composer require laravel/breeze -W',
                'publish' => 'php artisan breeze:install',
                'description' => 'Installing Laravel Breeze',
            ],
            [
                'install' => 'npm install && npm run build',
                'publish' => 'php artisan migrate:fr',
                'description' => 'Installing NPM Dependencies and Migrating Database',
            ],
            [
                'install' => 'composer require php-flasher/flasher-laravel',
                'publish' => 'php artisan flasher:install',
                'description' => 'Installing Flasher Laravel',
            ],
            [
                'install' => 'composer require php-flasher/flasher-sweetalert-laravel',
                'publish' => 'php artisan flasher:install',
                'description' => 'Installing Flasher SweetAlert Laravel',
            ],
            //			[
            //				'install' => 'composer require jantinnerezo/livewire-alert -W',
            //				'publish' => 'php artisan vendor:publish --tag=livewire-alert:assets',
            //				'description' => 'Installing Livewire Alert',
            //			],
            //			[
            //				'install' => 'composer require wire-elements/modal -W',
            //				'publish' => 'php artisan vendor:publish --tag=wire-elements-modal-config',
            //				'description' => 'Installing Wire Elements Modal',
            //			],
            //			[
            //				'install' => 'composer require realrashid/sweet-alert -W',
            //				'publish' => 'php artisan sweetalert:publish',
            //				'description' => 'Installing Sweet Alert',
            //			],
        ];

        foreach ($packages as $package) {
            $this->info("Starting: {$package['description']}");

            // Execute installation
            if ($this->executeCommand($package['install'])) {
                $this->info("Installed: {$package['description']}");

                // Execute publish commands
                $publishCommands = is_array($package['publish']) ? $package['publish'] : [$package['publish']];
                foreach ($publishCommands as $publishCommand) {
                    if ($this->executeCommand($publishCommand)) {
                        $this->info("Published: {$package['description']}");
                    } else {
                        $this->error("Failed to publish: {$package['description']}");
                    }
                }
            } else {
                $this->error("Failed to install: {$package['description']}");
            }
        }

        $this->info('All packages processed.');

        return Command::SUCCESS;
    }

    private function executeCommand(string $command): bool
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        return $process->isSuccessful();
    }
}
