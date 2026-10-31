<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Online File Download Limit (in Megabytes)
    |--------------------------------------------------------------------------
    |
    | Defines the maximum file size allowed for direct online browser streaming.
    | Files exceeding this limit will trigger pre-flight blocking with a friendly
    | explanation modal to prevent HTTP 413 Content Too Large errors.
    |
    */
    'max_size_mb' => (int) env('DOWNLOAD_MAX_SIZE_MB', 20),

    /*
    |--------------------------------------------------------------------------
    | File Streaming Chunk Size (in Bytes)
    |--------------------------------------------------------------------------
    |
    | Memory-safe chunk size used when streaming files to the browser.
    | Default is 1 MB (1048576 bytes).
    |
    */
    'chunk_size_bytes' => 1024 * 1024,
];
