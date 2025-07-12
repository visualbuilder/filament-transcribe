<x-filament::page>
    <form wire:submit.prevent="create">
        @include('filament-transcribe::components.audio_recorder')
    </form>
</x-filament::page>
