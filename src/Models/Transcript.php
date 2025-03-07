<?php
namespace Visualbuilder\FilamentTranscribe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;

class Transcript extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'transcribed_text',
        'transcribed_html',
        'status',
        'redact_pii',
        'transcriptable_id',
        'transcriptable_type',
        'speaker_names',
        'output_file',
        'duration_seconds',
        'title',
        'owner_id',
        'owner_type',
    ];

    protected $casts = [
        'status' => TranscriptStatus::class,
        'redact_pii' => 'boolean',
        'duration_seconds' => 'integer',
        'speaker_names' => 'json',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->fillOwnerOnCreating();
            $model->fillCustomRelationsOnCreating();
        });

        static::updating(function ($model) {
            $model->updateCustomRelations();
        });
    }

    protected function fillOwnerOnCreating(): void
    {
        if (auth()->check() && !$this->owner_id) {
            $this->owner_id = auth()->id();
            $this->owner_type = auth()->user()::class;
        }
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Customizable methods (intended to be overridden in extended models)
     */
    protected function fillCustomRelationsOnCreating(): void
    {
        // intentionally left blank
    }

    protected function updateCustomRelations(): void
    {
        // intentionally left blank
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio')
            ->singleFile()
            ->useDisk(config('filament-transcribe.aws.transcribe.inputDisk', 's3'));
    }
}
