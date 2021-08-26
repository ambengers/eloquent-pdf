<?php

namespace Ambengers\EloquentPdf;

use Ambengers\EloquentPdf\Exceptions\DomainLogicException;
use Barryvdh\Snappy\PdfWrapper;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractEloquentPdf
{
    protected $pdf;

    protected $model;

    protected $orientation = 'portrait';

    protected $options = [
        'footer-font-size' => 8,
        'encoding' => 'UTF-8',
    ];

    protected $view = '';

    protected $filename = 'pdf_report';

    protected $extension = 'pdf';

    protected $isStreaming = false;

    protected $isDownloading = false;

    public function __construct(PdfWrapper $pdf = null)
    {
        $this->pdf = $pdf;
    }

    /**
     * Array of data to be used on the view.
     *
     * @return array
     */
    abstract public function getData(): array;

    /**
     * The view file for the pdf.
     *
     * @return string
     */
    abstract public function getView(): string;

    /**
     * Handle the process of generating pdf.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->ensurePdfInstance();

        $this->pdf->setOrientation($this->getOrientation())->setOptions($this->getOptions());

        $this->pdf->loadView($this->getView(), $this->getData());

        if ($this->isStreaming()) {
            return $this->pdf->stream($this->getFilenameWithExtension());
        }

        if ($this->isDownloading()) {
            return $this->pdf->download($this->getFilenameWithExtension());
        }

        if (property_exists($this, 'mediaCollection') && isset($this->mediaCollection)) {
            return $this->saveToMediaCollection();
        }

        throw DomainLogicException::withMessage(
            'Unable to determine if PDF will be streamed, downloaded or saved to media collection.'
        );
    }

    public function model(Model $model)
    {
        $this->model = $model;

        return $this;
    }

    public function orientation(string $orientation): self
    {
        $this->orientation = $orientation;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    public function getOrientation(): string
    {
        return $this->orientation;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function stream(): self
    {
        $this->isStreaming = true;

        return $this;
    }

    public function download(): self
    {
        $this->isDownloading = true;

        return $this;
    }

    public function filename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function extension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getFilenameWithExtension(): string
    {
        return "{$this->getFilename()}.{$this->getExtension()}";
    }

    public function isStreaming(): bool
    {
        return $this->isStreaming;
    }

    public function isDownloading(): bool
    {
        return $this->isDownloading;
    }

    protected function ensurePdfInstance()
    {
        if (! $this->pdf) {
            $this->pdf = app(PdfWrapper::class);
        }

        return $this;
    }
}
