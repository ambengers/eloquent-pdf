<?php

namespace Ambengers\EloquentPdf\Tests\Features;

use Ambengers\EloquentPdf\AbstractEloquentPdf;
use Ambengers\EloquentPdf\InteractsWithMediaLibrary;
use Ambengers\EloquentPdf\Tests\BaseTestCase;
use Ambengers\EloquentPdf\Tests\Models\Post;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EloquentPdfTest extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();

        $this->post = (new Post)->fill([
            'title' => 'Test title',
            'body' => 'Test body'
        ]);
    }

    /** @test */
    public function it_streams_pdf()
    {
        SnappyPdf::fake();

        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->filename(time())
            ->stream();

        $response = $pdf->handle();

        $this->assertTrue($response instanceof StreamedResponse);

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
            ->filename(time())
            ->download();

        $response = $pdf->handle();

        $this->assertEquals(
            $response->headers->all()['content-disposition'][0],
            'attachment; filename="'.$pdf->getFilenameWithExtension().'"'
        );
    }

    /** @test */
    public function it_transfers_to_medialibrary()
    {
        $this->post->save();

        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->filename(time())
            ->toMediaCollection($collectionName = 'attachments');

        $pdf->handle();

        $this->assertDatabaseHas('media', [
            'model_type' => $this->post->getMorphClass(),
            'model_id' => $this->post->getKey(),
            'collection_name' => $collectionName,
            'name' => $pdf->getFilename(),
            'file_name' => $pdf->getFilenameWithExtension(),
            'disk' => config('filesystems.default'),
            'mime_type' => 'application/pdf',
        ]);
    }

    /** @test */
    public function it_can_include_additional_attributes_when_transferring_to_medialibrary()
    {
        $this->post->save();

        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->filename(time())
            ->withAttributes(['custom_attribute' => $attributeValue = 'Custom Attribute Value'])
            ->toMediaCollection($collectionName = 'attachments');

        $pdf->handle();

        $this->assertDatabaseHas('media', [
            'model_type' => $this->post->getMorphClass(),
            'model_id' => $this->post->getKey(),
            'collection_name' => $collectionName,
            'name' => $pdf->getFilename(),
            'custom_attribute' => $attributeValue,
            'file_name' => $pdf->getFilenameWithExtension(),
            'disk' => config('filesystems.default'),
            'mime_type' => 'application/pdf',
        ]);
    }


    /** @test */
    public function it_can_include_custom_properties_when_transferring_to_medialibrary()
    {
        $this->post->save();

        $pdf = app(PostPdf::class)
            ->model($this->post)
            ->filename(time())
            ->withCustomProperties($props = ['foo' => 1, 'bar' => 2])
            ->toMediaCollection($collectionName = 'attachments');

        $pdf->handle();

        $this->assertDatabaseHas('media', [
            'model_type' => $this->post->getMorphClass(),
            'model_id' => $this->post->getKey(),
            'collection_name' => $collectionName,
            'name' => $pdf->getFilename(),
            'file_name' => $pdf->getFilenameWithExtension(),
            'disk' => config('filesystems.default'),
            'mime_type' => 'application/pdf',
            'custom_properties' => json_encode($props),
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
            'body' => $this->model->body,
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
