<?php

if (!function_exists('bytes_to_kb')) {
    /**
     * Convert bytes to kilobytes.
     */
    function bytes_to_kb(int $bytes, int $precision = 2): float
    {
        return round($bytes / 1024, $precision);
    }
}


if (!function_exists('guessMediaFormat')) {
    function guessMediaFormat(?string $mimeType): string
    {
        return match ($mimeType) {
            'audio/mpeg'                            => 'mp3',
            'audio/mp4', 'video/mp4', 'audio/x-m4a' => 'mp4',
            'audio/wav'                             => 'wav',
            'audio/flac'                            => 'flac',
            'audio/ogg'                             => 'ogg',
            default                                 => 'mp3', // fallback
        };
    }
}
if (!function_exists('secondsToTimestamp')) {
    function secondsToTimestamp($secFloat)
    {
        return sprintf("%02d:%02d:%02d",
            intdiv($secFloat, 3600),
            intdiv($secFloat % 3600, 60),
            $secFloat % 60
        );
    }
}
