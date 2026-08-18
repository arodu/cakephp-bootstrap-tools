<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\VisualElement;

use BootstrapTools\View\VisualElement\VisualElementDatasetTrait;

/**
 * Test stub consuming VisualElementDatasetTrait.
 */
class DatasetStub
{
    use VisualElementDatasetTrait;

    public mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public function label(): string
    {
        return 'Stub label';
    }

    public function dataset(): array
    {
        return [
            'icon' => 'bi-stub',
            'color' => 'dark',
            'description' => 'dataset desc',
            'url' => '/stub',
        ];
    }
}
