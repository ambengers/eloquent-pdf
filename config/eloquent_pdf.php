<?php

return [
    /**
     * The namespace to use when generating Eloquent PDF class.
     */
    'namespace' => 'App\Pdf',

    /**
     * Config specifically for MediaLibrary...
     */
    'media' => [
        /**
         * The directory to temporarily store files before medialibrary transfer.
         * If set to null, storage_path('temp/pdf') will be used.
         */
        'temporary_directory' => null,
    ],
];
