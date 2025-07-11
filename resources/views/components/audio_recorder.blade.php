@php
    $storeUrl = route('filament-transcribe.recordings.store');
    $pingUrl = route('filament-transcribe.ping');
@endphp
<div x-data="recordComponent()" x-init="init()" class="space-y-4">
    <div>
        <label class="block text-sm font-medium">Audio Source</label>
        <select x-model="selectedDevice" class="filament-input mt-1">
            <template x-for="device in devices" :key="device.deviceId">
                <option :value="device.deviceId" x-text="device.label || 'Source'"></option>
            </template>
        </select>
    </div>
    <div x-show="recording" class="flex items-center space-x-2">
        <span class="text-danger-600 animate-pulse">&#9679;</span>
        <span x-text="timer"></span>
    </div>
    <div class="flex space-x-2">
        <button type="button" class="filament-button" x-show="!recording" @click="start()">Start</button>
        <button type="button" class="filament-button" x-show="recording" @click="stop()">Stop</button>
    </div>
</div>
<script>
function recordComponent() {
    return {
        devices: [],
        selectedDevice: null,
        recording: false,
        mediaRecorder: null,
        chunks: [],
        timer: '00:00',
        seconds: 0,
        timerInterval: null,
        keepAlive: null,
        init() {
            navigator.mediaDevices.enumerateDevices().then(list => {
                this.devices = list.filter(d => d.kind === 'audioinput');
                if (this.devices.length) this.selectedDevice = this.devices[0].deviceId;
            });
        },
        start() {
            navigator.mediaDevices.getUserMedia({ audio: { deviceId: this.selectedDevice ? { exact: this.selectedDevice } : undefined }})
                .then(stream => {
                    this.recording = true;
                    this.mediaRecorder = new MediaRecorder(stream);
                    this.mediaRecorder.ondataavailable = e => this.chunks.push(e.data);
                    this.mediaRecorder.onstop = this.upload.bind(this);
                    this.mediaRecorder.start();
                    this.startTimer();
                    this.keepAlive = setInterval(() => { fetch('@js($pingUrl)', {headers:{'X-Requested-With':'XMLHttpRequest'}}); }, 60000);
                });
        },
        stop() {
            this.recording = false;
            if (this.mediaRecorder) {
                this.mediaRecorder.stop();
            }
            clearInterval(this.keepAlive);
            this.stopTimer();
        },
        startTimer() {
            this.seconds = 0;
            this.timerInterval = setInterval(() => {
                this.seconds++;
                const h = String(Math.floor(this.seconds/3600)).padStart(2,'0');
                const m = String(Math.floor((this.seconds%3600)/60)).padStart(2,'0');
                this.timer = `${h}:${m}`;
            }, 1000);
        },
        stopTimer() { clearInterval(this.timerInterval); },
        upload() {
            const blob = new Blob(this.chunks, { type: 'audio/webm' });
            const form = new FormData();
            form.append('audio', blob, 'recording.webm');
            fetch('@js($storeUrl)', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content') },
                body: form
            }).then(r => r.json()).then(data => {
                if (data.redirect) window.location = data.redirect;
            });
        }
    }
}
</script>
