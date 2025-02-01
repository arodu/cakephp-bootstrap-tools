<?php
declare(strict_types=1);
/**
 * BootstrapTools CakePHP Plugin
 * 
 * @copyright 2025 Alberto Rodriguez
 * @author Alberto Rodriguez <arodu.dev@gmail.com>
 * @link https://github.com/arodu
 */

namespace BootstrapTools\View\Helper;

use Cake\Event\EventInterface;
use Cake\Utility\Hash;
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
    protected array $_defaultConfig = [
        'settings' => [
            'appName' => 'BootstrapTheme Demo',
            'appLogo' => 'BootstrapTools./logo.png',
        ],
        'autoRenderAssets' => false,
        'meta' => [],
        'css' => [],
        'scripts' => [],
    ];

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

    /**
     * @param array $options
     * @return void
     */
    public function renderAssets(array $options = [])
    {
        $options = Hash::merge($this->getConfig(), $options);
        foreach ($options['meta'] ?? [] as $name => $content) {
            $this->getView()->Html->meta($name, $content, ['block' => true]);
        }
        $this->getView()->Html->css($options['css'] ?? [], ['block' => true]);
        $this->getView()->Html->script($options['scripts'] ?? [], ['block' => true]);
    }

    /**
     * @param EventInterface $event
     * @param mixed $viewFile
     * @return void
     */
    public function beforeRender(EventInterface $event, $viewFile)
    {
        if ($this->getConfig('autoRenderAssets') ?? false) {
            $this->renderAssets();
        }
    }
}
