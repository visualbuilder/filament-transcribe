<?php

namespace Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource\Pages;


use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\On;
use Visualbuilder\FilamentTranscribe\Enums\TranscriptStatus;
use Visualbuilder\FilamentTranscribe\Filament\Actions\TranscribeAction;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;
use Visualbuilder\FilamentTranscribe\Jobs\TranscribeAudioJob;

class EditTranscript extends EditRecord
{
    protected static string $resource = TranscriptResource::class;

    public bool $showProgress = false;

    public bool $showTranscribe = false;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->showProgress = in_array($this->record->status, [TranscriptStatus::PENDING, TranscriptStatus::PROCESSING]);
        $this->showTranscribe = in_array($this->record->status, [TranscriptStatus::FAILED]);

        if ($this->record->status==TranscriptStatus::PENDING) {
            //Autostart transcribing
            $this->startJob();
        }


    }

    public function startJob()
    {
        // TODO  see if we can find out how long the wemb file is?
        // Read metadata with:- ffmpeg -i input.webm -c copy -map 0 -f webm fixed.webm
        /** Sample output
         * ffmpeg version 4.2.7-0ubuntu0.1 Copyright (c) 2000-2022 the FFmpeg developers
         * built with gcc 9 (Ubuntu 9.4.0-1ubuntu1~20.04.1)
         * configuration: --prefix=/usr --extra-version=0ubuntu0.1 --toolchain=hardened --libdir=/usr/lib/aarch64-linux-gnu --incdir=/usr/include/aarch64-linux-gnu --arch=arm64 --enable-gpl --disable-stripping --enable-avresample --disable-filter=resample --enable-avisynth --enable-gnutls --enable-ladspa --enable-libaom --enable-libass --enable-libbluray --enable-libbs2b --enable-libcaca --enable-libcdio --enable-libcodec2 --enable-libflite --enable-libfontconfig --enable-libfreetype --enable-libfribidi --enable-libgme --enable-libgsm --enable-libjack --enable-libmp3lame --enable-libmysofa --enable-libopenjpeg --enable-libopenmpt --enable-libopus --enable-libpulse --enable-librsvg --enable-librubberband --enable-libshine --enable-libsnappy --enable-libsoxr --enable-libspeex --enable-libssh --enable-libtheora --enable-libtwolame --enable-libvidstab --enable-libvorbis --enable-libvpx --enable-libwavpack --enable-libwebp --enable-libx265 --enable-libxml2 --enable-libxvid --enable-libzmq --enable-libzvbi --enable-lv2 --enable-omx --enable-openal --enable-opencl --enable-opengl --enable-sdl2 --enable-libdc1394 --enable-libdrm --enable-libiec61883 --enable-chromaprint --enable-frei0r --enable-libx264 --enable-shared
         * libavutil      56. 31.100 / 56. 31.100
         * libavcodec     58. 54.100 / 58. 54.100
         * libavformat    58. 29.100 / 58. 29.100
         * libavdevice    58.  8.100 / 58.  8.100
         * libavfilter     7. 57.100 /  7. 57.100
         * libavresample   4.  0.  0 /  4.  0.  0
         * libswscale      5.  5.100 /  5.  5.100
         * libswresample   3.  5.100 /  3.  5.100
         * libpostproc    55.  5.100 / 55.  5.100
         * Input #0, matroska,webm, from './storage/app/recordings/recording-20250728021508.webm':
         * Metadata:
         * encoder         : Chrome
         * Duration: N/A, start: 0.000000, bitrate: N/A
         * Stream #0:0(eng): Audio: opus, 48000 Hz, mono, fltp (default)
         * Output #0, webm, to 'fixed.webm':
         * Metadata:
         * encoder         : Lavf58.29.100
         * Stream #0:0(eng): Audio: opus, 48000 Hz, mono, fltp (default)
         * Stream mapping:
         * Stream #0:0 -> #0:0 (copy)
         * Press [q] to stop, [?] for help
         * size=      96kB time=00:00:05.99 bitrate= 130.9kbits/s speed=3.08e+03x
         * video:0kB audio:95kB subtitle:0kB other streams:0kB global headers:0kB muxing overhead: 1.327703%
         *
         *
         */

        //Search for time=00:00:05.99 in the output
        //Maybe put this code back and save extra info to the spatie media object



        Notification::make()
            ->title('Audio Submitted for Transcribing')
            ->body('Processing has started. You can wait on this page, or safely close it and return later.  Expect to wait around 15-30 seconds per minute of audio')
            ->success()
            ->send();

        TranscribeAudioJob::dispatch($this->record);
    }

    /**
     * Event Listener sent by broadcast
     *
     * @param $transcript
     *
     * @return void
     */
    #[On('transcriptUpdated')]
    public function onTranscriptUpdated($transcript): void
    {
        // Get the current state to preserve fields like coaching_session_id
        $currentState = $this->form->getState();

        // Merge in the new values for transcribed_html and status
        $this->form->fill(array_merge($currentState, [
            'transcribed_html' => $transcript['transcribed_html'],
            'status'           => $transcript['status'],
        ]));

        if (in_array($transcript['status'], [TranscriptStatus::COMPLETED->value, TranscriptStatus::FAILED->value])) {
            // Hide the spinner
            $this->showProgress = false;
            $this->showTranscribe = false;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            TranscribeAction::make()->visible(fn($livewire) => $livewire->showTranscribe)
        ];
    }

}
