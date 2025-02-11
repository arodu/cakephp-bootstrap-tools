<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * ModalAjax helper
 */
class ModalAjaxHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'target' => 'ajax-modal',
        'element' => 'BootstrapTools.modalAjax/default',
        'script' => 'BootstrapTools./js/bst-ajax-manager',
        'jsCallback' => false, // 'function (event, detail) { console.log(detail); }',

        'modalOptions' => [
            'size' => null, // 'modal-lg', 'modal-sm'
            'scrollable' => false,
            'centered' => false,
            'staticBackdrop' => false,
            'classes' => '',
            'dialogClasses' => '',
            'attributes' => [],
            'container' => [
                'class' => '',
            ],
        ],

        'closeOnSuccess' => false,
        'reloadPageOnSuccess' => false,
        'reloadPageOnClose' => false,
    ];

    /**
     * @var array
     */
    protected array $targets = [];

    /**
     * @param array|string $title
     * @param array|string|null|null $url
     * @param array $options
     * @return string
     */
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
            'data-modal-options' => json_encode($options['modalOptions'] ?? []), // Nueva línea
        ];
        unset($options['modalOptions']);

        return $this->getView()->Html->link($title, '#', $options);
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->getConfig('target');
    }

    /**
     * @param array $options
     * @return string
     */
    public function render(array $options = []): string
    {
        $output = '';
        foreach ($this->targets as $target => $value) {
            $output .= $this->renderItem($target, $options);
        }

        return $output;
    }

    /**
     * @param string|null $target
     * @param array $options
     * @return string
     */
    public function renderItem(string $target = null, array $options = []): string
    {
        $target = $target ?? $options['target'] ?? $this->getConfig('target');
        $jsCallback = $this->getConfig('jsCallback', null);
        $modalOptions = Hash::merge($this->getConfig('modalOptions'), $options['modalOptions'] ?? []);

        $modalOptions['title'] = $modalOptions['title'] ?? $options['title'] ?? '';
        unset($options['title']);

        $options = Hash::merge($this->getConfig(), $options, [
            'target' => $target,
            'jsCallback' => $jsCallback,
            'modalOptions' => $modalOptions,
        ]);

        return $this->getView()->element($this->getConfig('element'), $options);
    }
}
