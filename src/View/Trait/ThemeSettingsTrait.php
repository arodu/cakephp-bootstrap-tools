<?php

declare(strict_types=1);

namespace BootstrapTools\View\Trait;

use Cake\Core\Configure;
use Cake\Utility\Hash;

trait ThemeSettingsTrait
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     * 
     * Example:
     * protected array $_defaultConfig = [
     *     'configKey' => 'Bootstrap',
     *     'settings' => [
     *         'appName' => 'BootstrapTheme Demo',
     *         'appLogo' => 'BootstrapTools./logo.png',
     *     ],
     *     'meta' => [],
     *     'css' => [],
     *     'scripts' => [],
     * ];
    */

    /**
     * @inheritDoc
     */
    abstract public function getView(): \Cake\View\View;

    /**
     * @inheritDoc
     */
    abstract public function getConfig(?string $key = null, mixed $default = null): mixed;


    public function themeSettingsInitialize(array $config): void
    {
        $config = Hash::merge(Configure::read($this->getConfig('configKey'), []), $config);
        $this->setConfig($config);
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

    /**
     * Render meta tags
     * 
     * @return string
     */
    public function renderMeta(): string
    {
        $meta = $this->getConfig('meta') ?? [];
        $output = '';
        foreach ($meta as $name => $content) {
            $output .= $this->getView()->Html->meta($name, $content);
        }
        return $output;
    }

    /**
     * Render CSS files
     * 
     * @param array<string, mixed> $options
     * @return string
     */
    public function renderCss(array $options = []): string
    {
        $css = $this->getConfig('css') ?? [];
        $output = '';
        foreach ($css as $file) {
            $output .= $this->getView()->Html->css($file, $options);
        }
        return $output;
    }

    /**
     * Render scripts
     * 
     * @param array<string, mixed> $options
     * @return string
     */
    public function renderScripts(array $options = []): string
    {
        $scripts = $this->getConfig('scripts') ?? [];
        $output = '';
        foreach ($scripts as $file) {
            $output .= $this->getView()->Html->script($file, $options);
        }
        return $output;
    }
}
