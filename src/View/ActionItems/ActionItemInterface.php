<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionItems;

interface ActionItemInterface
{
    public function withOptions(array $options = []): ActionItemInterface;
    public function toArray(): array;

    public static function get(string $name): self;
    public static function set(string $name, array $options): void;
}