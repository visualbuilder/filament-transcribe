<?php
namespace Visualbuilder\FilamentTranscribe\Services;

use Aws\TranscribeService\TranscribeServiceClient;

class AwsTranscribeClientFactory
{
    public static function create(): TranscribeServiceClient
    {
        return new TranscribeServiceClient([
            'version'     => 'latest',
            'region'      => config('filament-transcribe.aws.transcribe.region'),
            'credentials' => [
                'key'    => config('filament-transcribe.aws.transcribe.key'),
                'secret' =>config('filament-transcribe.aws.transcribe.secret'),
            ],
        ]);
    }
}
