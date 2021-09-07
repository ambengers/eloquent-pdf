<?php

namespace Ambengers\EloquentPdf;

use Ambengers\EloquentPdf\Exceptions\DomainLogicException;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class AbstractEloquentPdf
{
    use ForwardsCalls;

    protected $pdf;

    protected $model;

    protected $orientation = 'portrait';

    protected $options = ['encoding' => 'UTF-8'];

    protected $view = '';

    protected $filename = 'pdf_file';

    protected $extension = 'pdf';

    protected $isStreaming = false;

    protected $isDownloading = false;

    public function __construct()
    {
        $this->ensurePdfWrapperInstance();

        if ($this->isInteractingWithMediaLibrary()) {
            $this->ensureFileAdderInstance();
        }
    }

    /**
     * Array of data to be used on the view.
     *
     * @return array
     */
    abstract public function getData(): array;

    /**
     * The view template file.
     *
     * @return string
     */
    abstract public function getView(): string;

    /**
     * Handle the process of generating the document.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ensurePdfWrapperInstance();

        $this->pdf->setOrientation($this->getOrientation())->setOptions($this->getOptions());

        $this->pdf->loadView($this->getView(), $this->getData());

        if ($this->isStreaming()) {
            return $this->pdf->stream($this->getFilenameWithExtension());
        }

        if ($this->isDownloading()) {
            return $this->pdf->download($this->getFilenameWithExtension());
        }

        if ($this->isInteractingWithMediaLibrary()) {
            return $this->saveToMediaCollection();
        }

        throw DomainLogicException::withMessage(
            'Unable to determine if PDF will be streamed, downloaded or saved to media collection.'
        );
    }

    /**
     * Set the eloquent model.
     *
     * @param  Model  $model
     * @return self
     */
    public function model(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the orientation.
     *
     * @param  string $orientation
     * @return self
     */
    public function orientation(string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Set options.
     *
     * @param  array  $options
     * @return self
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set the view template.
     *
     * @param  string $view
     * @return self
     */
    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Set the filename.
     *
     * @param  string $filename
     * @return self
     */
    public function filename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Set the file extension.
     *
     * @param  string $extension
     * @return self
     */
    public function extension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * Get the PDF orientation setting.
     *
     * @return string
     */
    public function getOrientation(): string
    {
        return $this->orientation;
    }

    /**
     * Get the PDF options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the response as stream.
     *
     * @return self
     */
    public function stream(): self
    {
        $this->isStreaming = true;

        return $this;
    }

    /**
     * Set the response as download.
     *
     * @return self
     */
    public function download(): self
    {
        $this->isDownloading = true;

        return $this;
    }

    /**
     * Get the eloquent model.
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * Get the document filename.
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get the document extension.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Get the document filename with extension.
     *
     * @return string
     */
    public function getFilenameWithExtension(): string
    {
        return "{$this->getFilename()}.{$this->getExtension()}";
    }

    /**
     * Determine if response is set to stream.
     *
     * @return bool
     */
    public function isStreaming(): bool
    {
        return $this->isStreaming;
    }

    /**
     * Determine if response is set to download.
     *
     * @return bool
     */
    public function isDownloading(): bool
    {
        return $this->isDownloading;
    }

    /**
     * Determine if class is interacting with media library.
     *
     * @return bool
     */
    public function isInteractingWithMediaLibrary()
    {
        return in_array(InteractsWithMediaLibrary::class, class_uses($this));
    }

    /**
     * Dynamically handle method calls.
     *
     * @param  string $method
     * @param  array $parameter
     * @return self
     */
    public function __call($method, $parameter)
    {
        if ($this->isInteractingWithMediaLibrary()) {
            $this->forwardCallTo($this->fileAdder, $method, $parameter);
        }

        return $this;
    }

    /**
     * Ensure PDF wrapper instance.
     *
     * @return self
     */
    protected function ensurePdfWrapperInstance()
    {
        if (! $this->pdf) {
            $this->pdf = app(PdfWrapper::class);
        }

        return $this;
    }
}
