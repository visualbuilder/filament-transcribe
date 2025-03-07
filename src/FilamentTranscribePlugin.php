<?php

namespace Visualbuilder\FilamentTranscribe;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Pages\SubNavigationPosition;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Visualbuilder\FilamentTranscribe\Filament\Resources\TranscriptResource;


class FilamentTranscribePlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool|Closure|null $navigation = null;

    // Navigation properties that can be overridden
    protected ?string $navigationGroup = null;
    protected ?string $navigationLabel = null;
    protected ?string $navigationIcon = null;
    protected ?string $navigationUrl = null;
    protected ?int $navigationSort = null;
    protected ?bool $navigationVisibleOnNavbar = null;
    protected ?string $navigationCluster = null;
    protected ?SubNavigationPosition $navigationSubnavPosition = null;



    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'filament-transcribe';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            config('filament-transcribe.transcript_resource', TranscriptResource::class),
        ]);
    }

    public function enableNavigation(bool|Closure $callback = true): static
    {
        $this->navigation = $callback;

        return $this;
    }

    public function shouldRegisterNavigation(): bool
    {
        return $this->evaluate($this->navigation) ?? false;
    }

    public function navigationGroup(?string $navigationGroup): static
    {
        $this->navigationGroup = $navigationGroup;
        return $this;
    }

    public function navigationLabel(string $label): static
    {
        $this->navigationLabel = $label;
        return $this;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;
        return $this;
    }

    public function navigationUrl(string $url): static
    {
        $this->navigationUrl = $url;
        return $this;
    }

    public function navigationSort(int $sort): static
    {
        $this->navigationSort = $sort;
        return $this;
    }

    public function navigationVisibleOnNavbar(bool $visible): static
    {
        $this->navigationVisibleOnNavbar = $visible;
        return $this;
    }

    public function cluster(?string $cluster): static
    {
        $this->cluster = $cluster;
        return $this;
    }

    public function navigationSubnavPosition(SubNavigationPosition $position): static
    {
        $this->navigationSubnavPosition = $position;
        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup ?? config('filament-transcribe.navigation.templates.group');
    }

    public function getNavigationLabel(): ?string
    {
        return $this->navigationLabel ?? config('filament-transcribe.navigation.label');
    }

    public function getNavigationIcon(): ?string
    {
        return $this->navigationIcon ?? config('filament-transcribe.navigation.icon');
    }

    public function getNavigationUrl(): ?string
    {
        return $this->navigationUrl ?? config('filament-transcribe.navigation.url');
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort ?? config('filament-transcribe.navigation.sort');
    }

    public function getNavigationVisibleOnNavbar(): ?bool
    {
        return $this->navigationVisibleOnNavbar ?? config('filament-transcribe.navigation.visible_on_navbar');
    }

    public function getCluster(): ?string
    {
        return $this->cluster ?? config('filament-transcribe.navigation.cluster');
    }

    public function getNavigationSubnavPosition(): ?SubNavigationPosition
    {
        return $this->navigationSubnavPosition ?? config('filament-transcribe.navigation.subnav_position');
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
