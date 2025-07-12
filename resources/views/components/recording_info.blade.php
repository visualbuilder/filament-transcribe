<div x-show="recording" class="text-center">
    <h2 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
        {{ __('vb-transcribe::audio_recorder.recording_session.title') }}
    </h2>
    <p>{{ __('vb-transcribe::audio_recorder.recording_session.note_save') }}</p>
    <p class="mb-4">{{ __('vb-transcribe::audio_recorder.recording_session.note_upload') }}</p>
    <p class="flex items-center justify-center space-x-2">
        <span class="text-danger-600 animate-pulse me-1">&#9679;</span>
        <span x-text="timer" class="text-3xl"></span>
    </p>
</div>
