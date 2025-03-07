<?php

namespace Visualbuilder\FilamentTranscribe\Jobs;

use Aws\TranscribeService\TranscribeServiceClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Events\TranscriptUpdated;
use Visualbuilder\FilamentTranscribe\Models\Transcript;
use Visualbuilder\FilamentTranscribe\Services\AwsTranscribeClientFactory;

class TranscribeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Transcript $transcript;

    /**
     * Create a new job instance.
     */
    public function __construct(Transcript $transcript)
    {
        $this->transcript = $transcript;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // 1) Retrieve the media; fail if missing
        $media = $this->getAudioMedia();
        if (!$media) {
            $this->failTranscript('No audio found');
            return;
        }

        // 2) Build a unique job name and get the audio URI
        $jobName = $this->buildUniqueJobName();
        $audioUri = $media->getFullUrl();

        // 3) Update transcript status to 'processing'
        $this->updateTranscriptStatus(TranscriptStatus::PROCESSING);

        // 4) Create AWS Transcribe client
        $transcribeClient = AwsTranscribeClientFactory::create();

        // 5) Start transcription job
        if (!$this->startTranscriptionJob($transcribeClient, $jobName, $audioUri, $media->mime_type)) {
            // If something went wrong starting the job, bail out
            return;
        }

        PollTranscriptionJob::dispatch($this->transcript, $jobName)->delay(now()->addSeconds(10));
    }

    /**
     * Retrieve the first media from the 'audio' collection via Spatie MediaLibrary.
     */
    private function getAudioMedia()
    {
        return $this->transcript->getFirstMedia('audio');
    }

    /**
     * Mark the transcript as failed, optionally passing a reason.
     */
    private function failTranscript(string $reason = 'failed')
    {
        Log::error($reason);
        $this->transcript->update([
            'status'           => 'failed',
            'transcribed_html' => $reason
        ]);

        TranscriptUpdated::dispatch($this->transcript);
    }

    /**
     * Build a unique job name (AWS Transcribe requires a unique name per account/region).
     */
    private function buildUniqueJobName()
    {
        return 'transcript_job_'.$this->transcript->id.'_'.time();
    }

    /**
     * Update transcript status and dispatch event.
     */
    private function updateTranscriptStatus($status)
    {
        $this->transcript->update(['status' => $status]);
        TranscriptUpdated::dispatch($this->transcript);
    }

    /**
     * Start an AWS Transcription job. Return boolean indicating success/failure.
     */
    private function startTranscriptionJob(TranscribeServiceClient $client, $jobName, $audioUri, $mimeType)
    {


        try {
            // Set up the base transcription job configuration
            $transcriptionConfig = [
                'TranscriptionJobName' => $jobName,
                'LanguageCode'         => $this->transcript->redact_pii ? config('filament-transcribe.aws.transcribe.languageCodeRedacted') : config('filament-transcribe.aws.transcribe.languageCode'),
                'MediaFormat'          => guessMediaFormat($mimeType),
                'Media'                => [
                    'MediaFileUri' => $audioUri,
                ],
                'OutputBucketName'     => config('filesystems.disks.'.config('filament-transcribe.aws.transcribe.outputDisk','s3').'.bucket'),
                'Settings'             => [
                    'ShowSpeakerLabels' => config('filament-transcribe.aws.transcribe.showSpeakerLabels'),
                    'MaxSpeakerLabels'  => $this->transcript->speakers,
                ]
            ];

            // Conditionally add ContentRedaction settings
            if ($this->transcript->redact_pii) {
                $transcriptionConfig['ContentRedaction'] = [
                    'RedactionOutput' => 'redacted',
                    'RedactionType'   => 'PII',
                    'PiiEntityTypes'  => config('filament-transcribe.aws.transcribe.redactType')
                ];
            }

            $client->startTranscriptionJob($transcriptionConfig);


        } catch (\Exception $e) {
            $this->failTranscript("Error starting transcription job: {$e->getMessage()}");
            return false;
        }

        return true;
    }
}
