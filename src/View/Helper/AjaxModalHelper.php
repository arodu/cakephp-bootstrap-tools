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
        'element' => 'BootstrapTools.modal/ajax',
        'eventJs' => 'modalResponse',
    ];

    protected array $targets = [];

    protected string $jsCallback = <<<JS_
    function (event, data, modal) { // javascript: Callback for modal response event
        if (data.status == 'success') {
            console.log('Success', data);
            modal.modal('hide');
        }
    }
    JS_;

    public function link(array|string $title, array|string|null $url = null, array $options = []): string
    {
        $target = $options['target'] ?? $this->getConfig('target');
        unset($options['target']);
        $this->targets[$target] = true;

        $options += [
            'class' => 'ajax-modal',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#' . $target,
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

    public function render(array $options = []): string
    {
        $options += $this->getConfig();
        $jsCallback = $this->getConfig('jsCallback') ?? $this->jsCallback;

        $output = '';
        foreach ($this->targets as $target => $value) {
            $output .= $this->getView()->element(
                $this->getConfig('element'),
                $options + [
                    'target' => $target,
                    'jsCallback' => $jsCallback,
                ]
            );
        }

        return $output;
    }
}
