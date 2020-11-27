<?php

namespace Ambengers\EloquentPdf\Console;

use Illuminate\Console\GeneratorCommand;

class EloquentPdfMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:eloquent-pdf {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent PDF class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'EloquentPdf';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/../../stubs/EloquentPdf.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return config('eloquent_pdf.namespace');
    }
}
