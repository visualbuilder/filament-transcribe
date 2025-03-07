<?php

namespace Visualbuilder\FilamentTranscribe\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{

    protected bool $shouldPublishConfigFile = true;

    protected bool $shouldPublishAssets = true;

    protected bool $shouldPublishMigrations = true;

    protected bool $askToRunMigrations = true;

    protected bool $shouldPublishSeeders = true;

    protected bool $askToRunSeeders = true;

    public function __construct()
    {
        $this->signature = 'filament-transcribe:install';

        $this->description = 'Install audio transcribe for Filament';

        parent::__construct();
    }

    public function handle()
    {
        $this->info("Installing Transcribe Package");

        if ($this->shouldPublishConfigFile) {
            $this->comment('Publishing config file...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-transcribe-config",
            ]);
        }

        if ($this->shouldPublishAssets) {
            $this->comment('Publishing assets...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-transcribe-assets",
            ]);
        }

        if ($this->shouldPublishMigrations) {
            $this->comment('Publishing migration...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-transcribe-migrations",
            ]);
        }

        if ($this->askToRunMigrations) {
            if ( $this->confirm('Would you like to run the migrations now?')) {
                $this->comment('Running migrations...');
                $this->call('migrate');
            }
        }


        $this->info("All Done");

        return Command::SUCCESS;
    }
}
