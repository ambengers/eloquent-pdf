<?php

namespace Ambengers\EloquentPdf\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Model::unguard();
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__.'/views', 'test');
    }
}
