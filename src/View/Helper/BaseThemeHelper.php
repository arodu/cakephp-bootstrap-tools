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
use Cake\View\View;

/**
 * BaseTheme helper
 * 
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class BaseThemeHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'options' => [
            'appName' => 'BaseTheme Demo',
            'appLogo' => 'BaseTheme./base-theme/images/logo.svg',
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
     * Get a configuration value
     *
     * @param string $key Configuration key
     * @return mixed
     */
    public function get(string $key, string $config = 'options')
    {
        return $this->getConfig($config . '.' . $key);
    }
}
