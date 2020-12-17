<?php

namespace Ambengers\EloquentPdf\Tests;

use Orchestra\Testbench\TestCase;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;
use Ambengers\EloquentPdf\EloquentPdfServiceProvider;
use Barryvdh\Snappy\ServiceProvider as PdfServiceProvider;

abstract class BaseTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            MediaLibraryServiceProvider::class,
            EloquentPdfServiceProvider::class,
            TestServiceProvider::class,
            PdfServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');

        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('filesystems.default', 'public');
        $app['config']->set('filesystems.disks.public.root', __DIR__.'/storage/app/public');

        $app['config']->set('medialibrary.max_file_size', 1024 * 1024 * 1000);
        $app['config']->set('medialibrary.media_model', \Spatie\MediaLibrary\Models\Media::class);
    }
}
