# Laravel Eloquent Pdf
This package provides an elegant way to generate PDF with eloquent models.
Uses [Laravel Snappy](https://github.com/barryvdh/laravel-snappy) to generate PDF and [Laravel Medialibrary](https://github.com/spatie/laravel-medialibrary) to associate PDF as model media.

[![Build Status](https://travis-ci.org/ambengers/eloquent-pdf.svg?branch=master)](https://travis-ci.org/ambengers/eloquent-pdf)
[![StyleCI](https://github.styleci.io/repos/316454658/shield?branch=master)](https://github.styleci.io/repos/316454658?branch=master)

## Installation

Via Composer

``` bash
$ composer require ambengers/eloquent-pdf
```

Optionally, you can publish the config file by running the following command.
``` bash
php artisan vendor:publish --tag=eloquent-pdf-config
```

## Usage

### Eloquent PDF class

You can generate your Eloquent PDF class using the command.
``` bash
$ php artisan make:eloquent-pdf PostPdf
```
By default, the class will be located at `App\Pdf` namespace. You can customize this in config file.

Your Eloquent PDF class will contain 2 methods:
 - `getData()` provides the data to be used on the view
 - `getView()` the name of the view file as pdf template

``` php
namespace App\Pdf;

class PostPdf extends AbstractEloquentPdf
{
    public function getData() : array
    {
        return [
            'title' => $this->model->title,
            'body'  => $this->model->body,
        ];
    }

    public function getView() : string
    {
        return 'posts.pdf';
    }
}
```

You can now use the Eloquent PDF class from your controller (or anywhere in your application).

### Downloading PDF

``` php
return app(PostPdf::class)
    ->model($post)
    ->download()
    ->handle();
```

### Print Preview PDF

``` php
return app(PostPdf::class)
    ->model($post)
    ->stream()
    ->handle();
```

### Eloquent PDF to Medialibrary

This package also offers an elegant way to associate the PDF file to the eloquent model using Medialibrary package.
To do that, you will need to use a trait to your Eloquent PDF class,

``` php
namespace App\Pdf;

use Ambengers\EloquentPdf\InteractsWithMediaLibrary;

class PostPdf extends AbstractEloquentPdf
{
    use InteractsWithMediaLibrary;

    // ...
}
```

Then on your controller, just provide the collection name in which the PDF file will be associated with.

``` php
return app(PostPdf::class)
    ->model($post)
    ->toMediaCollection('reports')
    ->handle();
```

### Customizations

If you need further customizations such as changing the default PDF filename, extension or setting PDF options,
you can override some methods from your Eloquent PDF class.

``` php
namespace App\Pdf;

class PostPdf extends AbstractEloquentPdf
{
    public function getOrientation(): string
    {
        return 'landscape';
    }

    public function getOptions(): array
    {
        return [
            'footer-right'     => 'Right footer text goes here!',
            'footer-font-size' => 8,
            'encoding'         => 'UTF-8',
        ];
    }

    public function getFilename(): string
    {
        return 'new-file-name';
    }

    public function getExtension(): string
    {
        return 'odt';
    }
}
```

Alternatively, if you want to only customize during runtime, you can chain some setter methods when you call your Eloquent PDF class

``` php
return app(PostPdf::class)
    ->model($post)
    ->orientation('landscape')
    ->options(['footer-font-size' => 8])
    ->filename('some-cool-filename')
    ->toMediaCollection('reports')
    ->handle();
```

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## License

Please see the [license file](license.md) for more information.
