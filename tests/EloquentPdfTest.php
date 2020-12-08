<?php

namespace Ambengers\EloquentPdf\Tests;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Ambengers\EloquentPdf\Tests\Models\Post;
use Ambengers\EloquentPdf\AbstractEloquentPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ambengers\EloquentPdf\InteractsWithMediaLibrary;

class EloquentPdfTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();

        $this->loadMigrationsFrom([
            '--database' => 'sqlite',
            '--path'     => realpath(__DIR__.'/Migrations'),
        ]);

        $this->post = (new Post)->fill([
            'title' => 'Test title',
            'body'  => 'Test body'
        ]);
    }


    /** @test */
    public function it_streams_pdf()
    {
        SnappyPdf::fake();

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
        SnappyPdf::fake();

        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->download();

        $response = $pdf->handle();

        $this->assertEquals(
            $response->headers->all()['content-disposition'][0],
            'attachment; filename="'.$pdf->getFilenameWithExtension().'"'
        );
    }

    /** @test */
    public function it_transfers_to_media_library()
    {
        $this->post->save();

        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->toMediaCollection($collectionName = 'attachments');

        $pdf->handle();

        $this->assertDatabaseHas('media', [
            'model_type'      => $this->post->getMorphClass(),
            'model_id'        => $this->post->getKey(),
            'collection_name' => $collectionName,
            'name'            => $pdf->getFilename(),
            'file_name'       => $pdf->getFilenameWithExtension(),
            'disk'            => config('filesystems.default'),
            'mime_type'       => 'application/pdf',
        ]);
    }
}

class PostPdf extends AbstractEloquentPdf
{
    use InteractsWithMediaLibrary;

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
