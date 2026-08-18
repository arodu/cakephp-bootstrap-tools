<?php
declare(strict_types=1);

namespace BootstrapTools\View\Dropdown;

interface DropdownBuilderInterface
{
    /**
     * Set options for the table builder.
     *
     * @param array $options Options for the table builder.
     * @return void
     */
    public function setOptions(array $options): self;

    /**
     * Get options for the table builder.
     *
     * @return array Options for the table builder.
     */
    public function getOptions(): array;

    /**
     * Set dropdown items.
     *
     * @param array $items Menu items configuration.
     * @return $this
     */
    public function items(array $items): self;
}
