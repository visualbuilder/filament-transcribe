<x-filament::page>
    <form wire:submit.prevent="record">
        @include('filament-transcribe::components.audio_recorder')
    </form>
</x-filament::page>
