<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * TabsAjax helper
 */
class TabsAjaxHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'target' => 'ajax-tabs',
        'script' => 'BootstrapTools./js/bst-script',
        'content' => [
            'element' => 'BootstrapTools.tabsAjax/content',
        ],
        'nav' => [
            'element' => 'BootstrapTools.tabsAjax/nav',
            'class' => 'nav nav-tabs',
        ],
    ];

    /**
     * Registered tabs.
     *
     * @var array<int|string, array<string, mixed>>
     */
    protected array $tabs = [];

    /**
     * @param string $key
     * @param array $options
     * @return self
     */
    public function addItem(string $key, array $options = []): self
    {
        if (empty($options['label'])) {
            $options['label'] = $key;
        }

        $this->tabs[$key] = $options;

        return $this;
    }

    /**
     * @return string
     */
    public function renderNav(array $options = []): string
    {
        return $this->getView()->element($this->getConfig('nav.element'), [
            'tabs' => $this->tabs ?? [],
            'config' => Hash::merge($this->getConfig(), $options),
        ]);
    }

    /**
     * @param string $key
     * @param array $options
     * @return string
     */
    protected function renderNavItem(string $key, array $options = []): string
    {
        $keyId = '#' . $key;

        $options = array_merge(
            [
                'class' => 'nav-link',
                'id' => $key . '-tab',
                'data-bs-toggle' => 'tab',
                'data-bs-target' => $keyId,
                'role' => 'tab',
                'aria-controls' => $key,
                'aria-selected' => 'true',
                'tabindex' => '-1',
            ],
            $options,
        );

        $label = $options['label'] ?? $key;
        if (is_array($label)) {
            $label = $this->getView()->Html->tag('span', $label['text'], $label['options'] ?? []);
        }

        if ($options['active'] ?? false) {
            $options['class'] = $options['class'] . ' active';
            $options['aria-selected'] = 'true';
        }

        if ($options['disabled'] ?? false) {
            $options['class'] = $options['class'] . ' disabled';
            $options['aria-disabled'] = 'true';
            $options['tabindex'] = '-1';
        }
        unset($options['label'], $options['active'], $options['disabled'], $options['url']);

        return $this->getView()->Html->tag(
            'li',
            $this->getView()->Html->link($label, $keyId, $options),
            ['class' => 'nav-item', 'role' => 'presentation'],
        );
    }

    /**
     * @param array $options
     * @return string
     */
    public function renderContent(array $options = []): string
    {
        return $this->getView()->element($this->getConfig('content.element'), [
            'tabs' => $this->tabs ?? [],
            'config' => Hash::merge($this->getConfig(), $options),
        ]);
    }
}
