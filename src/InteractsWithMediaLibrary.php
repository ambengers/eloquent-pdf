<?php

namespace Ambengers\EloquentPdf;

use Ambengers\EloquentPdf\Exceptions\DomainLogicException;
use Ambengers\EloquentPdf\Exceptions\TemporaryFileMissedException;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia\HasMedia;

trait InteractsWithMediaLibrary
{
    protected $mediaCollection;

    protected $customProperties = [];

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
     * Set the custom properties in medialibrary.
     *
     * @param  array  $properties
     * @return $this
     */
    public function withCustomProperties(array $properties)
    {
        $this->customProperties = $properties;

        return $this;
    }

    /**
     * Process saving to media collection;.
     *
     * @return \Spatie\MediaLibrary\Models\Media
     *
     * @throws \Ambengers\EloquentPdf\Exceptions\DomainLogicException
     * @throws \Ambengers\EloquentPdf\Exceptions\TemporaryFileMissedException
     */
    public function saveToMediaCollection()
    {
        if (! $this->model instanceof HasMedia) {
            throw DomainLogicException::withMessage(
                class_basename($this->model).' must be an instance of Spatie\MediaLibrary\HasMedia\HasMedia.'
            );
        }

        $storage = Storage::disk($this->getTemporaryDisk());

        $temporaryPath = $this->getTemporaryPath($this->getFilenameWithExtension());

        // We want to save the pdf in public disk first and let medialibrary
        // package pick it up and transfer to the desired storage..
        $saved = $this->pdf->save($storage->path($temporaryPath));

        if (! $saved || ! $storage->exists($temporaryPath)) {
            // In some cases, there will be a race condition where PDF is not saved in temp
            // directory when it gets picked up by medialibrary. So here let's try for it
            // and throw an exception so the developer will be able to act accordingly.
            throw TemporaryFileMissedException::withMessage(
                'File was not saved in temporary location: '.$storage->path($temporaryPath)
            );
        }

        $media = $this->model->addMedia($storage->path($temporaryPath))
            ->usingFileName($this->getFilenameWithExtension())
            ->withCustomProperties($this->customProperties)
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
