<?php

namespace Visualbuilder\FilamentTranscribe\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Livewire\Mechanisms\DataStore;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Visualbuilder\FilamentTranscribe\FilamentTranscribeServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Visualbuilder\\FilamentTranscribe\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        View::share('errors', new ViewErrorBag);
        $dataStore = app(DataStore::class);
        app()->instance(DataStore::class, $dataStore);
    }

    protected function getPackageProviders($app)
    {
        return [
            FilamentTranscribeServiceProvider::class,
            MediaLibraryServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
