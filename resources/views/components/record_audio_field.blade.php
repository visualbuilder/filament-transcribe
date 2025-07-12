@php
    $pingUrl = route('filament-transcribe.ping');
    $statePath = $getStatePath();
@endphp
<div
    x-data="{
        pingUrl: @js($pingUrl),
        statePath: @js($statePath),
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
            // Locate the select element rendered by the Filament form via its ref
            this.selectEl = this.$refs.select;
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
            const current = this.selectedDevice;
            this.selectEl.innerHTML = '';
            this.devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.deviceId;
                option.text = device.label || 'Source';
                this.selectEl.appendChild(option);
            });
            if (current) {
                this.selectEl.value = current;
            } else if (this.devices.length) {
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
                // Re-enumerate devices once permission has been granted to
                // ensure device labels are available.
                navigator.mediaDevices.enumerateDevices().then(list => {
                    this.devices = list.filter(d => d.kind === 'audioinput');
                    this.populateSelect();
                });
                this.keepAlive = setInterval(() => {
                    fetch(this.pingUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
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
            const file = new File([blob], `recording-${Date.now()}.webm`, { type: blob.type });
            this.$wire.upload(this.statePath, file, () => {}, () => {}, (e) => console.error(e));
        }
    }"
    x-init="init()"
    class="space-y-4"
>
    <div>
        <label class="block text-sm font-medium leading-6 text-gray-900 mb-1">Audio Source</label>
        <x-filament::forms::native-select x-ref="select" class="w-full"></x-filament::forms::native-select>
    </div>
    <div x-show="recording" class="flex items-center space-x-2">
        <span class="text-danger-600 animate-pulse">&#9679;</span>
        <span x-text="timer"></span>
    </div>
    <div class="flex space-x-2">
        <x-filament::button type="button" x-show="!recording" @click="start()">Start</x-filament::button>
        <x-filament::button type="button" x-show="recording" @click="stop()">Stop</x-filament::button>
    </div>
</div>
