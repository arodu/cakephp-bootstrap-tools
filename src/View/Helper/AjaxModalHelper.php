<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

/**
 * AjaxModal helper
 */
class AjaxModalHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'target' => 'ajax-modal',
    ];

    public function link(array|string $title, array|string|null $url = null, array $options = []): string
    {
        $options += [
            'class' => 'ajax-modal',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#' . $this->getConfig('target'),
            'escape' => false,
            'data-url' => $this->getView()->Url->build($url),
        ];
        $url = '#';

        return $this->getView()->Html->link($title, $url, $options);
    }

    public function getTarget(): string
    {
        return $this->getConfig('target');
    }
}
