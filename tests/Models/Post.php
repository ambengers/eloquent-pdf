<?php

namespace Ambengers\EloquentPdf\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections() : void
    {
        $this->addMediaCollection('attachments');
    }
}
