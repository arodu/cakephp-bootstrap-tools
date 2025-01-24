<?php
declare(strict_types=1);
/**
 * ArchitectUI CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

namespace BootstrapTools\View\Helper;

use Cake\Event\EventInterface;
use Cake\View\Helper;

/**
 * BootstrapThemeHelper helper
 * 
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class BootstrapThemeHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'settings' => [
            'appName' => 'BootstrapTheme Demo',
            'appLogo' => 'BootstrapTools./logo.png',
        ],
        'meta' => [],
        'css' => [],
        'scripts' => [],
    ];

    public function beforeRender(EventInterface $event, $viewFile)
    {
        foreach ($this->getConfig('meta') ?? [] as $name => $content) {
            $this->getView()->Html->meta($name, $content, ['block' => true]);
        }
        $this->getView()->Html->css($this->getConfig('css') ?? [], ['block' => true]);
        $this->getView()->Html->script($this->getConfig('scripts') ?? [], ['block' => true]);
    }

    /**
     * Get settings value
     * 
     * @param string $key Configuration key
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getConfig('settings.' . $key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->setConfig('settings.' . $key, $value);
    }
}
