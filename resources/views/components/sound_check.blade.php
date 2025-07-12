<div>
    <div x-show="checkingLevels" class="text-center">
        <h2 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
            {{ __('vb-transcribe::audio_recorder.sound_check.title') }}
        </h2>
        <p class="mb-4">{{ __('vb-transcribe::audio_recorder.sound_check.note') }}</p>
    </div>

    <div x-show="recording || checkingLevels" class="flex justify-center space-x-0.5 mb-4">
        <template x-for="i in totalSegments" :key="i">
            <div class="vu-meter-bar"
                 :class="{
                    'vu-green': i <= vuSegments && i <= 8,
                    'vu-amber': i <= vuSegments && i > 8 && i <= 12,
                    'vu-red': i <= vuSegments && i > 12
                }">
            </div>
        </template>
    </div>
</div>
