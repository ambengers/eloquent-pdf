<?php

namespace Ambengers\EloquentPdf;

use Ambengers\EloquentPdf\Exceptions\DomainLogicException;
use Ambengers\EloquentPdf\Exceptions\TemporaryFileMissedException;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\FileAdder\FileAdder;
use Spatie\MediaLibrary\HasMedia\HasMedia;

trait InteractsWithMediaLibrary
{
    protected $fileAdder;

    protected $mediaCollection;

    /**
     * Set the mediaCollection property.
     *
     * @param  string  $mediaCollection
     * @return $this
     */
    public function toMediaCollection(string $mediaCollection)
    {
        $this->mediaCollection = $mediaCollection;

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

        // We want to save the document within the temporary directory first and let the
        // medialibrary package pick it up and transfer to the desired storage..
        $temporaryPath = $this->saveTemporaryFile();

        if (! file_exists($temporaryPath)) {
            // In some cases, there will be a race condition where PDF is not saved in temp
            // directory when it gets picked up by medialibrary. So here let's try for it
            // and throw an exception so the developer will be able to act accordingly.
            throw TemporaryFileMissedException::withMessage(
                "File was not saved in temporary location: {$temporaryPath}"
            );
        }

        return $this->fileAdder->setSubject($this->model)
            ->setFile($temporaryPath)
            ->usingFileName($this->getFilenameWithExtension())
            ->toMediaCollection($this->mediaCollection);
    }

    protected function saveTemporaryFile()
    {
        $this->ensureTemporaryDirectoryExists();

        $path = $this->getTemporaryPath($this->getFilenameWithExtension());

        $this->pdf->save($path);

        return $path;
    }

    protected function ensureTemporaryDirectoryExists()
    {
        if (! file_exists($this->getTemporaryDirectory()) || ! is_dir($this->getTemporaryDirectory())) {
            mkdir($this->getTemporaryDirectory(), 0755, true);
        }
    }

    protected function getTemporaryPath($filename)
    {
        return $this->getTemporaryDirectory().'/'.$filename;
    }

    protected function getTemporaryDirectory()
    {
        return config('eloquent_pdf.media.temporary_directory') ?? storage_path('temp/pdf');
    }

    protected function ensureFileAdderInstance()
    {
        if (! $this->fileAdder) {
            $this->fileAdder = app(FileAdder::class);
        }
    }
}
