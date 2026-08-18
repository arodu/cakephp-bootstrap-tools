<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Trait;

use BootstrapTools\View\Trait\ThemeSettingsTrait;
use Cake\Core\InstanceConfigTrait;
use Cake\View\View;

/**
 * Stub class consuming ThemeSettingsTrait for testing.
 */
class ThemeSettingsTraitStub
{
    use InstanceConfigTrait;
    use ThemeSettingsTrait;

    /**
     * Default configuration.
     */
    protected array $_defaultConfig = [
        'configKey' => 'Bootstrap',
        'settings' => [
            'appName' => 'Demo',
        ],
        'meta' => [],
        'css' => [],
        'scripts' => [],
    ];

    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getView(): View
    {
        return $this->view;
    }
}
