<?php
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

    public function items(array $items): self;
}