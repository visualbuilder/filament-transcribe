# Transcribe audio files with speaker labels

[![Latest Version on Packagist](https://img.shields.io/packagist/v/visualbuilder/filament-transcribe.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/filament-transcribe)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/visualbuilder/filament-transcribe/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/visualbuilder/filament-transcribe/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/visualbuilder/filament-transcribe/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/visualbuilder/filament-transcribe/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/visualbuilder/filament-transcribe.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/filament-transcribe)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/filament-transcribe.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/filament-transcribe)


## Installation

You can install the package via composer:

```bash
composer require visualbuilder/filament-transcribe
```

You can publish config, views and migrations with:

```bash
php artisan filament-transcribe:install
```

## Broadcasting
Transcripts will typically take 15-30 seconds per minute of audio.  To allow our transcript page to receive updates, use of websockets broadcasting messages is recommended.
Details for setting up broadcasts can be found in the [Laravel docs](https://laravel.com/docs/11.x/broadcasting).
Quick setup steps for pusher:-

### Setup a Pusher app for Broadcasts
Note you can use any other broadcast services, we just happen to use Pusher.  The TranscriptUpdated Event will send to which ever service is configured.
https://dashboard.pusher.com/apps

Create an app and paste the connection details into your .env file, be sure to check the cluster name is set to your region.
```bash
PUSHER_APP_ID="your-pusher-app-id"
PUSHER_APP_KEY="your-pusher-key"
PUSHER_APP_SECRET="your-pusher-secret"
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME="https"
PUSHER_APP_CLUSTER="mt1"

BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher
```
### Install Pusher and Echo

```bash
composer require pusher/pusher-php-server
npm install --save-dev laravel-echo pusher-js
npm run build
```

### Setup Broadcast Auth and Route

In the Broadcast provider add your auth provider (we have admin guard you may not)
```php
    public function boot(): void
    {
        Broadcast::routes([ 'middleware' => ['web', 'auth:admin']]);
```

In routes/channels.php create the channel

```php
Broadcast::channel('transcript.{transcriptId}', function ($user, $transcriptId) {
    return true;
    // Optionally check if the user has permission to see this transcript
    //return Transcript::where('id', $transcriptId)->where('owner_id', $user->id)->exists();
});
```

### Setup Filament to use broadcasts in the panel provider

```php
$panel->
...
 ->broadcasting()
```

## Background Job Processing
Due to the long time required to complete the transcript, synchronous jobs will time out if not completed within a minute.  
(Note: annoyingly AWS does not provide a % complete indicator on these jobs so we can't give the user any meaningful progress bar)
We've therefore included a separate job to check the transcript progress.  This job is called and scheduled by the process job and is best handled as a background task, so good to use a queue like 
database or redis instead of the default sync queue.

```bash
QUEUE_CONNECTION=database
```


When recording audio through the provided recorder, the browser will also save a `recording-<timestamp>.webm` file locally before uploading. This ensures you retain a copy if the upload fails.


## Usage

```php
$filamentTranscribe = new Visualbuilder\FilamentTranscribe();
echo $filamentTranscribe->echoPhrase('Hello, Visualbuilder!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Visual Builder](https://github.com/visualbuilder)
- [All Contributors](../../contributors)
+
## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
