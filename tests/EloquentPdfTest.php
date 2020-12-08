<?php

namespace Ambengers\EloquentPdf\Tests;

use Orchestra\Testbench\TestCase;
use Barryvdh\Snappy\ServiceProvider as PdfServiceProvider;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Ambengers\EloquentPdf\Tests\Models\Post;
use Ambengers\EloquentPdf\AbstractEloquentPdf;
use Ambengers\EloquentPdf\EloquentPdfServiceProvider;

class EloquentPdfTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            EloquentPdfServiceProvider::class,
            TestServiceProvider::class,
            PdfServiceProvider::class,
        ];
    }

    /** @test */
    public function it_streams_pdf()
    {
        SnappyPdf::fake();

        $post = (new Post)->fill(['title' => 'Test title','body' => 'Test body']);

        $pdf = app(PostPdf::class)
            ->model($post)
            ->stream();

        $response = $pdf->handle();

        $this->assertTrue($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse);
        $this->assertEquals($response->headers->all()['content-disposition'][0], 'inline; filename="'.$pdf->getFilenameWithExtension().'"');
    }
}

class PostPdf extends AbstractEloquentPdf
{
    /**
     * Array of data to be used on the view.
     *
     * @return array
     */
    public function getData() : array
    {
        return [
            'title' => $this->model->title,
            'body'  => $this->model->body,
        ];
    }

    /**
     * The name of the view file for the pdf
     *
     * @return string
     */
    public function getView() : string
    {
        return 'test::post-pdf';
    }
}
