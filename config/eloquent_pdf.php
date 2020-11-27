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
         * The disk to use to store PDF files temporarily before medialibrary.
         */
        'temporary_disk' => 'public',

        /**
         * The name of the folder within the temporary disk.
         */
        'temporary_folder' => 'temp',
    ],
];
