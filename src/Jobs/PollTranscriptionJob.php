<?php
namespace Visualbuilder\FilamentTranscribe\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Events\TranscriptUpdated;
use Visualbuilder\FilamentTranscribe\Models\Transcript;
use Visualbuilder\FilamentTranscribe\Services\AwsTranscribeClientFactory;

class PollTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(protected Transcript $transcript, protected  string $jobName)
    {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = AwsTranscribeClientFactory::create();

        try {
            $result = $client->getTranscriptionJob(['TranscriptionJobName' => $this->jobName]);
            $job    = $result->get('TranscriptionJob');
            $status = $job['TranscriptionJobStatus'] ?? 'UNKNOWN';
        } catch (\Exception $e) {
            Log::error("Error polling transcription job: {$e->getMessage()}");
            $this->failTranscript("Transcription Failed. ".$e->getMessage());
            return;
        }

        if ($status === 'COMPLETED') {
            $this->handleJobCompletion();
            return;
        } elseif ($status === 'FAILED') {
            $this->failTranscript("Transcription Failed. ".print_r($job, true));
        } else {
            // If still in progress, re-dispatch this job to check again later
            self::dispatch($this->transcript, $this->jobName)->delay(now()->addSeconds(5));
        }
    }

    /**
     * Handle the transcription job after AWS reports COMPLETED:
     *  - Fetch the JSON from S3
     *  - Extract plain text
     *  - Generate HTML paragraphs by audio segments
     *  - Update the transcript record
     */
    private function handleJobCompletion()
    {
        $jsonKey = ($this->transcript->redact_pii?'redacted-':''). $this->jobName . '.json';

        if (! Storage::disk(config('filament-transcribe.aws.transcribe.outputDisk'))->exists($jsonKey)) {
            $this->failTranscript("Transcription JSON file not found in S3: {$jsonKey}");
            return;
        }

        $jsonContents = Storage::disk(config('filament-transcribe.aws.transcribe.outputDisk'))->get($jsonKey);
        $data         = json_decode($jsonContents, true);

        // 1) Plain text
        $transcribedText = $data['results']['transcripts'][0]['transcript'] ?? '';

        // 2) Build HTML by audio segments + get duration in minutes
        [$finalHtml, $durationSeconds] = $this->generateHtmlByAudioSegments($data);

        // 3) Save result
        $this->transcript->update([
            'transcribed_text' => $transcribedText,
            'transcribed_html' => $finalHtml,
            'output_file'      => $jsonKey,
            'duration_seconds' => $durationSeconds,
            'status'           => TranscriptStatus::COMPLETED,
        ]);

        TranscriptUpdated::dispatch($this->transcript);
    }

    /**
     * Mark the transcript as failed, optionally passing a reason.
     */
    private function failTranscript(string $reason = 'failed')
    {
        Log::error($reason);
        $this->transcript->update([
            'status' => 'failed',
            'transcribed_html'=>$reason
        ]);

        TranscriptUpdated::dispatch($this->transcript);
    }

        /**
     * Generate paragraphs based on "audio_segments" in the AWS Transcribe JSON.
     * Each segment becomes one paragraph, with a timestamp above it.
     *
     * Returns an array: [HTML_string, duration_minutes].
     */
    private function generateHtmlByAudioSegments(array $data)
    {
        $audioSegments = $data['results']['audio_segments'] ?? [];
        $items         = $data['results']['items'] ?? [];

        // Determine the maximum speaker index from the audio segments.
        $maxSpeakerIndex = 0;
        foreach ($audioSegments as $segment) {
            if (isset($segment['speaker_label'])) {
                $num = (int) str_replace('spk_', '', $segment['speaker_label']);
                $maxSpeakerIndex = max($maxSpeakerIndex, $num);
            }
        }

        // Build the speaker name map.
        $userSpeakerNames = $this->transcript->speaker_names;
        $speakerNameMap = [];

        if (!empty($userSpeakerNames) && is_array($userSpeakerNames)) {
            // Use user-defined speaker names.
            foreach ($userSpeakerNames as $index => $name) {
                $speakerNameMap['spk_' . $index] = $name;
            }
            // For any speaker label beyond the user-defined names, add generic names.
            $definedCount = count($userSpeakerNames);
            if ($maxSpeakerIndex >= $definedCount) {
                for ($i = $definedCount; $i <= $maxSpeakerIndex; $i++) {
                    $speakerNameMap['spk_' . $i] = 'Speaker ' . ($i + 1);
                }
            }
        } else {
            // Fallback default mapping.
            $speakerNameMap['spk_0'] = 'Speaker 1';
            $speakerNameMap['spk_1'] = 'Speaker 2';
            if ($maxSpeakerIndex > 1) {
                for ($i = 2; $i <= $maxSpeakerIndex; $i++) {
                    $speakerNameMap['spk_' . $i] = 'Speaker ' . ($i + 1);
                }
            }
        }

        $outputHtml = [];
        $maxEndTime = 0.0;

        // Build paragraph text from each audio segment
        foreach ($audioSegments as $segment) {
            $startTime    = isset($segment['start_time']) ? (float)$segment['start_time'] : 0.0;
            $endTime      = isset($segment['end_time'])   ? (float)$segment['end_time']   : 0.0;
            $speakerLabel = $segment['speaker_label'] ?? 'unknown';
            $itemIndexes  = $segment['items'] ?? [];

            // Track overall max end time
            if ($endTime > $maxEndTime) {
                $maxEndTime = $endTime;
            }

            // Build the paragraph text by combining the items that fall in this segment
            $paragraphText   = $this->buildTextFromItemIndexes($items, $itemIndexes);
            $timestamp       = secondsToTimestamp($startTime);
            $speakerDisplay  = $speakerNameMap[$speakerLabel] ?? $speakerLabel;

            // Wrap the snippet in <span> + <p>
            $outputHtml[] = "<p style='font-size:small'>[$timestamp] Speaker: <span class='{$speakerLabel}'>{$speakerDisplay}</span></p>";
            $outputHtml[] = "<p>{$paragraphText}</p>";
        }

        // Final HTML
        $finalHtml       = implode("\n", $outputHtml);
        // Duration in whole minutes
        $durationSeconds = floor($maxEndTime);

        return [$finalHtml, $durationSeconds];
    }

    /**
     * Build a text string from a list of item indexes (which reference the main items array).
     * We gather all the "pronunciation" items, then append punctuation from "punctuation" items.
     */
    private function buildTextFromItemIndexes(array $allItems, array $indexes)
    {
        $words = [];
        foreach ($indexes as $index) {
            if (! isset($allItems[$index])) {
                continue;
            }
            $item = $allItems[$index];

            if ($item['type'] === 'pronunciation') {
                // Add the word
                $content = $item['alternatives'][0]['content'] ?? '';
                $words[] = $content;
            } elseif ($item['type'] === 'punctuation') {
                // Append punctuation to the last word in $words
                if (count($words) > 0) {
                    $punctuationSymbol = $item['alternatives'][0]['content'] ?? '';
                    $words[count($words) - 1] .= $punctuationSymbol;
                }
            }
        }

        // Join words with spaces
        return implode(' ', $words);
    }

}
