<?php

namespace Visualbuilder\FilamentTranscribe\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TranscriptStatus: string implements HasLabel, HasColor
{

    use EnumSubset;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING    => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED  => 'Completed',
            self::FAILED     => 'Failed',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::PENDING    => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED  => 'success',
            self::FAILED     => 'danger',
        };
    }
}
