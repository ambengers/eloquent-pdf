<?php

namespace Ambengers\EloquentPdf;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Ambengers\EloquentPdf\Exceptions\DomainLogicException;

trait InteractsWithMediaLibrary
{
    protected $mediaCollection;

    /**
     * Set the mediaCollection property.
     *
     * @param  string $mediaCollection
     * @return $this
     */
    public function toMediaCollection(string $mediaCollection)
    {
        $this->mediaCollection = $mediaCollection;

        return $this;
    }

    /**
     * Process saving to media collection;
     *
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function saveToMediaCollection()
    {
        if (! $this->model instanceof HasMedia) {
            throw DomainLogicException::withMessage(
                class_basename($this->model).' must be an instance of Spatie\MediaLibrary\HasMedia\HasMedia'
            );
        }

        $storage = Storage::disk($this->getTemporaryDisk());

        $temporaryPath = $this->getTemporaryPath($this->getFilenameWithExtension());

        $this->pdf->save(
            // We want to save the pdf in public disk first and let medialibrary
            // package pick it up and transfer to the desired storage..
            $storage->path($temporaryPath)
        );

        $media = $this->model->addMedia($storage->path($temporaryPath))
            ->usingFileName($this->getFilenameWithExtension())
            ->toMediaCollection($this->mediaCollection);

        $storage->deleteDirectory($this->getTemporaryFolder());

        return $media;
    }

    protected function getTemporaryPath($filename)
    {
        return $this->getTemporaryFolder().'/'.$filename;
    }

    protected function getTemporaryFolder()
    {
        return config('eloquent_pdf.media.temporary_path', 'temp');
    }

    protected function getTemporaryDisk()
    {
        return config('eloquent_pdf.media.temporary_disk', 'public');
    }
}
