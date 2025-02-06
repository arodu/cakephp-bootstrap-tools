<?php

declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

trait ThemeSettingsTrait
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'configKey' => 'Bootstrap',
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
    public function beforeLayout(EventInterface $event, $viewFile)
    {
        parent::beforeLayout($event, $viewFile);
        if ($this->getConfig('autoRenderAssets') ?? false) {
            $this->renderAssets();
        }
    }
}
