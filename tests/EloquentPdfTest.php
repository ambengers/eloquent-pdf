<?php

namespace Ambengers\EloquentPdf\Tests;

use Orchestra\Testbench\TestCase;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Ambengers\EloquentPdf\Tests\Models\Post;
use Ambengers\EloquentPdf\AbstractEloquentPdf;
use Ambengers\EloquentPdf\EloquentPdfServiceProvider;
use Barryvdh\Snappy\ServiceProvider as PdfServiceProvider;

class EloquentPdfTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        SnappyPdf::fake();

        $this->post = (new Post)->fill(['title' => 'Test title','body' => 'Test body']);
    }

    /** @test */
    public function it_streams_pdf()
    {
        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->stream();

        $response = $pdf->handle();

        $this->assertTrue($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse);
        $this->assertEquals(
            $response->headers->all()['content-disposition'][0],
            'inline; filename="'.$pdf->getFilenameWithExtension().'"'
        );
    }

    /** @test */
    public function it_downloads_pdf()
    {
        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->download();

        $response = $pdf->handle();

        $this->assertEquals(
            $response->headers->all()['content-disposition'][0],
            'attachment; filename="'.$pdf->getFilenameWithExtension().'"'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            EloquentPdfServiceProvider::class,
            TestServiceProvider::class,
            PdfServiceProvider::class,
        ];
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
