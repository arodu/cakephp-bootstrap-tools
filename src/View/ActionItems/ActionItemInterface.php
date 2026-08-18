<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionItems;

interface ActionItemInterface
{
    /**
     * Merge options into the action item.
     *
     * @param array $options
     * @return static
     */
    public function withOptions(array $options = []): ActionItemInterface;

    /**
     * Return the action item as an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Get an action item by registry name.
     *
     * @param string $name
     * @return static
     */
    public static function get(string $name): self;

    /**
     * Set an action item in the registry.
     *
     * @param string $name
     * @param array $options
     * @return void
     */
    public static function set(string $name, array $options): void;
}
