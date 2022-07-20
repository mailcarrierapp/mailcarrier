<?php

use MailCarrier\Enums\AttachmentLogStrategy;

return [
    /*
    |--------------------------------------------------------------------------
    | Social Auth
    |--------------------------------------------------------------------------
    |
    | Determine what Socialite driver should be used for Social Auth.
    |
    */
    'social_auth_driver' => env('MAILCARRIER_SOCIAL_AUTH_DRIVER'),

    'api_endpoint' => [
        /*
        |--------------------------------------------------------------------------
        | API auth guard
        |--------------------------------------------------------------------------
        |
        | Set the guard that must be applied to protected the API endpoint.
        | Use `null` to disable it.
        |
        */
        'auth_guard' => env('MAILCARRIER_AUTH_GUARD', 'auth:sanctum'),

        /*
        |--------------------------------------------------------------------------
        | API auth extra middleware
        |--------------------------------------------------------------------------
        |
        | Set the middleware that must be applied to the API endpoint.
        | Use `null` to disable it.
        |
        */
        'extra_middleware' => [],
    ],

    'logs' => [
        /*
        |--------------------------------------------------------------------------
        | Prunable time period
        |--------------------------------------------------------------------------
        |
        | Determine how old the logs must be to prune them.
        | You can use a human syntax like "30 days" or "6 months".
        |
        */
        'prunable_period' => '3 months',
    ],

    'attachments' => [
        /*
        |--------------------------------------------------------------------------
        | Max attachments size
        |--------------------------------------------------------------------------
        |
        | Define the maximum attachments files in kb.
        | For example, 1024 * 5 = 5MB.
        |
        */
        'max_size' => 1024 * 5,

        /*
        |--------------------------------------------------------------------------
        | Accepted mimetypes
        |--------------------------------------------------------------------------
        |
        | Define the accepted mimetypes.
        | Set it to null to accept any kind of file.
        |
        */
        'mimetypes' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'text/csv',
            'application/vnd.ms-excel', // xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
            'application/pdf',
            'application/zip',
        ],

        /*
        |--------------------------------------------------------------------------
        | Default attachments storage
        |--------------------------------------------------------------------------
        |
        | Define the default storage that will be used to
        | upload or download attachments.
        | When an attachment is sent and saved for logs, it will be uploaded.
        | When an attachment is a reference, it will be fetched.
        |
        */
        'disk' => env('MAILCARRIER_FILESYSTEM_DISK', env('FILESYSTEM_DISK')),

        /*
        |--------------------------------------------------------------------------
        | Additional attachments disks
        |--------------------------------------------------------------------------
        |
        | Define the additional disks available to pull referenced attachments.
        |
        */
        'additional_disks' => [],

        /*
        |--------------------------------------------------------------------------
        | Upload path
        |--------------------------------------------------------------------------
        |
        | Define the default path where the standard attachments will be uploaded.
        |
        */
        'path' => null,

        /*
        |--------------------------------------------------------------------------
        | Log's attachment strategy
        |--------------------------------------------------------------------------
        |
        | Define the strategy to retain attachments for logs:
        | - AttachmentLogStrategy::Inline to save them encoded in the database.
        | - AttachmentLogStrategy::Upload to upload them in the storage (if standard).
        | - AttachmentLogStrategy::None to save only their names and sizes.
        |
        */
        'log_strategy' => AttachmentLogStrategy::Inline,
    ],

    'queue' => [
        /*
        |--------------------------------------------------------------------------
        | Emails queue
        |--------------------------------------------------------------------------
        |
        | Allow emails to be enqueued.
        |
        */
        'enabled' => true,

        /*
        |--------------------------------------------------------------------------
        | Force email queue
        |--------------------------------------------------------------------------
        |
        | Force email to be always enqueued.
        | If true, the `enqueued` boolean of the API endpoint will be ignored.
        |
        */
        'force' => env('MAILCARRIER_FORCE_QUEUE', false),

        /*
        |--------------------------------------------------------------------------
        | Queue name
        |--------------------------------------------------------------------------
        |
        | Set the queue name.
        | Set it to `null` to use the default value.
        |
        */
        'name' => null,

        /*
        |--------------------------------------------------------------------------
        | Queue connection
        |--------------------------------------------------------------------------
        |
        | Set the queue connection, e.g. `sqs` or `redis`.
        | Set it to `null` to use the default value.
        |
        */
        'connection' => null,
    ],
];
