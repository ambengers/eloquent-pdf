<?php

namespace Ambengers\EloquentPdf;

use Ambengers\EloquentPdf\Console\EloquentPdfMakeCommand;
use Illuminate\Support\ServiceProvider;

class EloquentPdfServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/eloquent_pdf.php' => config_path('eloquent_pdf.php'),
        ], 'eloquent-pdf-config');

        if ($this->app->runningInConsole()) {
            $this->commands(EloquentPdfMakeCommand::class);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/eloquent_pdf.php', 'eloquent_pdf');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        //
    }
}
