<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\Helper\FormatOptionsTrait;
use Cake\View\Helper;

/**
 * Stub helper consuming FormatOptionsTrait.
 */
class FormatOptionsStub extends Helper
{
    use FormatOptionsTrait;

    /**
     * @var array<string>
     */
    protected array $helpers = ['Html'];

    /**
     * @param array $item
     * @return array
     */
    public function format(array $item): array
    {
        return $this->formatOptions($item);
    }
}
