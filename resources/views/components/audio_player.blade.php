@php
    $record = $getRecord();
    $mediaItem = $record?->getFirstMedia('audio');
    $audioUrl = $mediaItem ? $mediaItem->getTemporaryUrl(now()->addMinutes(60)) : null;
@endphp
<div>
@if ($audioUrl)
    <audio controls style="width: 100%; margin-top: 0.75rem;">
        <source src="{{ $audioUrl }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <p class="p-1">{{$mediaItem->name}}  ({{bytes_to_kb($mediaItem->size,0)}}Kb)</p>
@endif
</div>
