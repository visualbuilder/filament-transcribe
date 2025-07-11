@php
    $storeUrl = route('filament-transcribe.recordings.store');
    $pingUrl = route('filament-transcribe.ping');
@endphp
<div
    x-data="{
        storeUrl: @js($storeUrl),
        pingUrl: @js($pingUrl),
        devices: [],
        selectedDevice: null,
        recording: false,
        mediaRecorder: null,
        stream: null,
        chunks: [],
        timer: '00:00:00',
        seconds: 0,
        timerInterval: null,
        keepAlive: null,
        selectEl: null,
        init() {
            this.selectEl = this.$el.querySelector('select[name=\'device\']');
            if (this.selectEl) {
                this.selectEl.addEventListener('change', e => this.selectedDevice = e.target.value);
            }
            navigator.mediaDevices.enumerateDevices().then(list => {
                this.devices = list.filter(d => d.kind === 'audioinput');
                this.populateSelect();
            });
        },
        populateSelect() {
            if (!this.selectEl) return;
            this.devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || 'Source';
                this.selectEl.appendChild(option);
            });
            if (this.devices.length && !this.selectedDevice) {
                this.selectEl.value = this.devices[0].deviceId;
                this.selectedDevice = this.devices[0].deviceId;
            }
        },
        start() {
            navigator.mediaDevices.getUserMedia({
                audio: { deviceId: this.selectedDevice ? { exact: this.selectedDevice } : undefined }
            }).then(stream => {
                this.recording = true;
                this.stream = stream;
                this.mediaRecorder = new MediaRecorder(stream);
                this.mediaRecorder.ondataavailable = e => this.chunks.push(e.data);
                this.mediaRecorder.onstop = this.upload.bind(this);
                this.mediaRecorder.start();
                this.startTimer();
                this.keepAlive = setInterval(() => {
                    fetch(this.pingUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                }, 60000);
            });
        },
        stop() {
            this.recording = false;
            if (this.mediaRecorder) {
                this.mediaRecorder.stop();
            }
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            clearInterval(this.keepAlive);
            this.stopTimer();
        },
        startTimer() {
            this.seconds = 0;
            this.timerInterval = setInterval(() => {
                this.seconds++;
                const h = String(Math.floor(this.seconds / 3600)).padStart(2, '0');
                const m = String(Math.floor((this.seconds % 3600) / 60)).padStart(2, '0');
                const s = String(this.seconds % 60).padStart(2, '0');
                this.timer = `${h}:${m}:${s}`;
            }, 1000);
        },
        stopTimer() { clearInterval(this.timerInterval); },
        upload() {
            const blob = new Blob(this.chunks, { type: 'audio/webm;codecs=opus' });
            const form = new FormData();
            form.append('audio', blob, 'recording.webm');
            fetch(this.storeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content') },
                body: form
            }).then(r => r.json()).then(data => {
                if (data.redirect) window.location = data.redirect;
            });
        }
    }"
    x-init="init()"
    class="space-y-4"
>
    {!! $form !!}
    <div x-show="recording" class="flex items-center space-x-2">
        <span class="text-danger-600 animate-pulse">&#9679;</span>
        <span x-text="timer"></span>
    </div>
    <div class="flex space-x-2">
        <x-filament::button type="button" x-show="!recording" @click="start()">Start</x-filament::button>
        <x-filament::button type="button" x-show="recording" @click="stop()">Stop</x-filament::button>
    </div>
</div>
