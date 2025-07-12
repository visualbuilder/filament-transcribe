<div>
    @php
        $pingUrl = route('filament-transcribe.ping');
    @endphp
    <div
        x-data="{
        pingUrl: @js($pingUrl),
        keepAliveInterval: @js(config('filament-transcribe.keep_alive_interval_ms')),
        devices: [],
        selectedDevice: null,
        recording: $wire.$entangle('recording'),
        mediaRecorder: null,
        stream: null,
        chunks: [],
        audioCtx: null,
        analyser: null,
        vuSegments: 0,
        totalSegments: 15,
        vuSensitivity: 4,
        checkingLevels: $wire.$entangle('checkingLevels'),
        meterRAF: null,
        timer: '00:00:00',
        seconds: 0,
        timerInterval: null,
        keepAlive: null,
        selectEl: null,
        statusMessage: '',
        init() {
            // Locate the select element rendered by the Filament form.
            // Using a generic query avoids coupling to the name attribute
            // which may vary once the form is rendered by Livewire.
            this.selectEl = this.$el.querySelector('select');
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
                option.text = device.label || 'Microphone';
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
            if (this.checkingLevels) {
                this.stopLevelCheck();
            }
            this.chunks = [];
            this.statusMessage = '';
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
                this.initVuMeter(stream);
                // Re-enumerate devices once permission has been granted to
                // ensure device labels are available.
                navigator.mediaDevices.enumerateDevices().then(list => {
                    this.devices = list.filter(d => d.kind === 'audioinput');
                    this.populateSelect();
                });
                this.keepAlive = setInterval(async () => {
                    try {
                        await fetch(this.pingUrl, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            credentials: 'same-origin'
                        });
                    } catch (error) {
                        console.error('Ping failed', error);
                    }
                }, this.keepAliveInterval);

            });
        },
        stop() {
            this.recording = false;
            this.checkingLevels = false;
            if (this.mediaRecorder) {
                this.mediaRecorder.stop();
            }
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            clearInterval(this.keepAlive);
            this.stopTimer();
            this.stopVuMeter();
        },
        startLevelCheck() {
            this.statusMessage = '';
            navigator.mediaDevices.getUserMedia({
                audio: { deviceId: this.selectedDevice ? { exact: this.selectedDevice } : undefined }
            }).then(stream => {
                this.checkingLevels = true;
                this.stream = stream;
                this.initVuMeter(stream);
                navigator.mediaDevices.enumerateDevices().then(list => {
                    this.devices = list.filter(d => d.kind === 'audioinput');
                    this.populateSelect();
                });
            });
        },
        stopLevelCheck() {
            this.checkingLevels = false;
            if (this.stream) {
                this.stream.getTracks().forEach(t => t.stop());
                this.stream = null;
            }
            this.stopVuMeter();
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
        downloadRecording(blob) {
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.style.display = 'none';
            link.href = url;
            link.download = `recording-${Date.now()}.webm`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        },
        upload() {
            const blob = new Blob(this.chunks, { type: 'audio/webm;codecs=opus' });
            this.downloadRecording(blob);
            const file = new File([blob], `recording-${Date.now()}.webm`, { type: blob.type });
            this.$wire.upload('recordingFile', file, () => this.$wire.create(), () => {}, (e) => console.error(e));
            this.chunks = [];
        },
        initVuMeter(stream) {
            try {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const source = this.audioCtx.createMediaStreamSource(stream);
                this.analyser = this.audioCtx.createAnalyser();
                this.analyser.fftSize = 256;
                source.connect(this.analyser);
                this.updateMeter();
            } catch (e) {
                console.error('VU meter init failed', e);
            }
        },
        updateMeter() {
            if (!this.analyser) return;
            const data = new Uint8Array(this.analyser.fftSize);
            this.analyser.getByteTimeDomainData(data);
            let sum = 0;
            for (let i = 0; i < data.length; i++) {
                const val = data[i] - 128;
                sum += val * val;
            }
            const rms = Math.sqrt(sum / data.length);
            const level = Math.min(1, (rms / 128) * this.vuSensitivity);
            this.vuSegments = Math.round(level * this.totalSegments);
            this.meterRAF = requestAnimationFrame(this.updateMeter.bind(this));
        },
        stopVuMeter() {
            if (this.audioCtx) {
                this.audioCtx.close();
                this.audioCtx = null;
            }
            cancelAnimationFrame(this.meterRAF);
            this.analyser = null;
            this.vuSegments = 0;
        }
    }"
        x-init="init()"
        class="space-y-4"
    >
        {{ $this->form }}


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
        <div class="flex space-x-2 justify-center">
            <x-filament::button type="button" x-show="!recording && !checkingLevels" @click="startLevelCheck()">
                {{ __('vb-transcribe::audio_recorder.buttons.check_levels') }}
            </x-filament::button>
            {{--        <x-filament::button type="button" x-show="checkingLevels" @click="stopLevelCheck()">Stop Check</x-filament::button>--}}
            <x-filament::button type="button" icon="heroicon-m-microphone" x-show="checkingLevels" @click="start()">
                {{ __('vb-transcribe::audio_recorder.buttons.start_recording') }}
            </x-filament::button>
            <x-filament::button type="button" x-show="recording" @click="stop()">
                {{ __('vb-transcribe::audio_recorder.buttons.stop') }}
            </x-filament::button>
        </div>
        <p x-show="statusMessage" x-text="statusMessage" class="text-danger-600"></p>
    </div>
</div>
