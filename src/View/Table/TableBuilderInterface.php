<?php
declare(strict_types=1);

namespace BootstrapTools\View\Table;

interface TableBuilderInterface
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
}
