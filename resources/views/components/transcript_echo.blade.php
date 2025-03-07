@props(['transcriptId'])

<div
    x-data="{}"
    x-init="
        window.addEventListener('EchoLoaded', () => {
            window.Echo.private('transcript.' + @js($transcriptId))
                .listen('.transcript.updated', (event) => {
                    console.log('Transcript updated event received')
                    setTimeout(() => $wire.dispatch('transcriptUpdated', {transcript: event.transcript}), 300);
                })
        });
    "
>
</div>
