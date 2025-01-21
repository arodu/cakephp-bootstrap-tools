<?php

declare(strict_types=1);

namespace BootstrapTools\Utility;

class Color
{
    public const PRIMARY = 'primary';
    public const SECONDARY = 'secondary';
    public const SUCCESS = 'success';
    public const DANGER = 'danger';
    public const WARNING = 'warning';
    public const INFO = 'info';
    public const LIGHT = 'light';
    public const DARK = 'dark';

    public const COLORS = [
        self::PRIMARY,
        self::SECONDARY,
        self::SUCCESS,
        self::DANGER,
        self::WARNING,
        self::INFO,
        self::LIGHT,
        self::DARK,
    ];

    public const TYPE_BOOTSTRAP = 'bootstrap';
    public const TYPE_HEX = 'hex';
    public const TYPE_RGB = 'rgb';
    public const TYPE_RGBA = 'rgba';
    public const TYPE_UNKNOWN = 'unknown';

    private string $color;
    private string $type;

    public function __construct(string $color, string $type = null)
    {
        $this->color = $color;
        $this->type = $type ?? $this->deduceType($color);
    }

    protected function deduceType(string $color): string
    {
        if (in_array($color, self::COLORS)) {
            return self::TYPE_BOOTSTRAP;
        }

        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return self::TYPE_HEX;
        }

        if (preg_match('/^rgb\((\d{1,3}), (\d{1,3}), (\d{1,3})\)$/', $color)) {
            return self::TYPE_RGB;
        }

        if (preg_match('/^rgba\((\d{1,3}), (\d{1,3}), (\d{1,3}), (0|1|0\.\d+)\)$/', $color)) {
            return self::TYPE_RGBA;
        }

        return self::TYPE_UNKNOWN;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
