<?php

// config for Visualbuilder/FilamentTranscribe
return [

    /**
     * AWS Transcribe Client Credentials
     */
    'aws'                    => [
        'transcribe' => [
            'key'                  => env('AWS_ACCESS_KEY_ID'),
            'secret'               => env('AWS_SECRET_ACCESS_KEY'),
            'region'               => env('AWS_DEFAULT_REGION', 'eu-west-2'),

            'inputDisk'            => 's3',
            'outputDisk'           => 's3',
            'showSpeakerLabels'    => true,

            'languageCode'         => 'en-GB',

            /**
             * GB not supported for redaction ATM
             */
            'languageCodeRedacted' => 'en-US',


            /**
             * Redaction Options, choose from options below or ALL
             */
            'redactType'           => ['ALL']
            /**
             * PII Redaction Types
             *
             * ADDRESS
             * A physical address, such as 100 Main Street, Anytown, USA or Suite #12, Building 123. An address can include a street, building, location, city, state, country, county, zip, precinct, neighborhood, and more.
             *
             * ALL
             * Redact or identify all PII types listed in this table.
             *
             * BANK_ACCOUNT_NUMBER
             * A US bank account number. These are typically between 10 - 12 digits long, but Amazon Transcribe also recognizes bank account numbers when only the last 4 digits are present.
             *
             * BANK_ROUTING
             * A US bank account routing number. These are typically 9 digits long, but Amazon Transcribe also recognizes routing numbers when only the last 4 digits are present.
             *
             * CREDIT_DEBIT_CVV
             * A 3-digit card verification code (CVV) that is present on VISA, MasterCard, and Discover credit and debit cards. In American Express credit or debit cards, it is a 4-digit numeric code.
             *
             * CREDIT_DEBIT_EXPIRY
             * The expiration date for a credit or debit card. This number is usually 4 digits long and formatted as month/year or MM/YY. For example, Amazon Transcribe can recognize expiration dates such as 01/21, 01/2021, and Jan 2021.
             *
             * CREDIT_DEBIT_NUMBER
             * The number for a credit or debit card. These numbers can vary from 13 to 16 digits in length, but Amazon Transcribe also recognizes credit or debit card numbers when only the last 4 digits are present.
             *
             * EMAIL
             * An email address, such as efua.owusu@email.com.
             *
             * NAME
             * An individual's name. This entity type does not include titles, such as Mr., Mrs., Miss, or Dr. Amazon Transcribe does not apply this entity type to names that are part of organizations or addresses. For example, Amazon Transcribe recognizes the John Doe Organization as an organization, and Jane Doe Street as an address.
             *
             * PHONE
             * A phone number. This entity type also includes fax and pager numbers.
             *
             * PIN
             * A 4-digit personal identification number (PIN) that allows someone to access their bank account information.
             *
             * SSN
             * A Social Security Number (SSN) is a 9-digit number that is issued to US citizens, permanent residents, and temporary working residents. Amazon Transcribe also recognizes Social Security Numbers when only the last 4 digits are present.
             */
        ]
    ],

    /**
     * laravel disk to save audio file - must be an s3 disk for aws
     *
     */
    'disk'                   => 's3',


    /**
     * |--------------------------------------------------------------------------
     * | Maximum Audio File Upload Size (in KB)
     * |--------------------------------------------------------------------------
     * |
     * | This value defines the maximum allowed file size for audio uploads (in KB).
     * |
     * | To ensure this works correctly, you must also configure your server settings:
     * |
     * | 1. PHP (`php.ini`):
     * |    - Set `upload_max_filesize` and `post_max_size` to at least the same size or larger.
     * |      Example:
     * |      upload_max_filesize = 128M
     * |      post_max_size = 128M
     * |
     * | 2. Web Server Configuration:
     * |
     * |    **Nginx (`nginx.conf` or site-specific conf):**
     * |      client_max_body_size 128M;
     * |
     * |    **Apache (`.htaccess` or VirtualHost):**
     * |      php_value upload_max_filesize 128M
     * |      php_value post_max_size 128M
     * |      LimitRequestBody 134217728  # (value in bytes; 128MB = 128 * 1024 * 1024)
     * |
     * | After updating these settings, restart your PHP-FPM and web server services:
     * |    - Nginx: `sudo service nginx reload`
     * |    - Apache: `sudo service apache2 restart`
     * |    - PHP-FPM: `sudo service php8.2-fpm restart` (replace 8.2 with your PHP version)
     * |
     */
    'max_audio_file_size_kb' => 128000, // 128 MB in KB

    /**
     * Which files are allowed to be uploaded
     */
    'allowed_file_types'     => [
        'audio/mpeg'   => 'mp3',
        'audio/mp4'    => 'm4a',
        'audio/x-m4a'  => 'm4a',
        'audio/x-aiff' => 'aiff',
        'audio/flac'   => 'flac',
        'audio/wav'    => 'wav',
        'video/mp4'    => 'mp4',
        'audio/ogg'    => 'ogg',
    ],

    /**
     * Navigation options for Resource Page
     */
    'navigation'             => [
        'enabled'           => true,
        'visible_on_navbar' => true,
        'icon'              => 'heroicon-o-microphone',
        'group'             => null,
        'label'             => 'Transcripts',
        'url'               => 'transcripts',
        'cluster'           => null,
        'sort'              => 1,
        'subnav_position'   => \Filament\Pages\SubNavigationPosition::Top
    ],

    'transcript_model'    => \Visualbuilder\FilamentTranscribe\Models\Transcript::class,
    'transcript_resource' => \Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource::class,

    /**
     * Which user classes will be available on the transcript owner morph select
     */
    'user_models'         => [
        [
            'model'           => config('auth.providers.users.model'),
            'title_attribute' => 'name', // or 'email'
        ],
        //Add additional user models

    ],


];
