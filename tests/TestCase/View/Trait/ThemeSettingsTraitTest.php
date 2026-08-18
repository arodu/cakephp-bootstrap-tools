<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Trait;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * ThemeSettingsTraitTest
 */
class ThemeSettingsTraitTest extends TestCase
{
    private View $view;

    private ThemeSettingsTraitStub $stub;

    public function setUp(): void
    {
        parent::setUp();
        Configure::delete('Bootstrap');
        $this->view = new View();
        $this->view->loadHelper('Html');
        $this->stub = new ThemeSettingsTraitStub($this->view);
    }

    public function tearDown(): void
    {
        Configure::delete('Bootstrap');
        parent::tearDown();
    }

    public function testThemeSettingsInitializeMergesConfigure(): void
    {
        Configure::write('Bootstrap', [
            'appName' => 'From Configure',
            'appLogo' => 'logo.png',
        ]);

        $this->stub->themeSettingsInitialize(['settings' => ['appName' => 'From Config']]);

        // $config['settings'] gana sobre Configure en caso de conflicto.
        $this->assertSame('From Config', $this->stub->get('appName'));
        $this->assertSame('logo.png', $this->stub->get('appLogo'));
    }

    public function testGetReturnsDefaultWhenKeyMissing(): void
    {
        $this->assertNull($this->stub->get('missing'));
        $this->assertSame('fallback', $this->stub->get('missing', 'fallback'));
    }

    public function testSetUpdatesSetting(): void
    {
        $this->stub->set('appName', 'New Name');

        $this->assertSame('New Name', $this->stub->get('appName'));
    }

    public function testRenderMetaWithEmptyMeta(): void
    {
        $this->assertSame('', $this->stub->renderMeta());
    }

    public function testRenderMetaRendersTags(): void
    {
        $this->stub->setConfig('meta', [
            'description' => 'Hello world',
            'robots' => 'index',
        ]);

        $output = $this->stub->renderMeta();

        $this->assertStringContainsString('name="description"', $output);
        $this->assertStringContainsString('content="Hello world"', $output);
        $this->assertStringContainsString('name="robots"', $output);
    }

    public function testRenderCssWithEmptyCss(): void
    {
        $this->assertSame('', $this->stub->renderCss());
    }

    public function testRenderCssRendersLinks(): void
    {
        $this->stub->setConfig('css', ['BootstrapTools./css/app']);

        $output = $this->stub->renderCss(['block' => false]);

        $this->assertStringContainsString('rel="stylesheet"', $output);
        $this->assertStringContainsString('css/app', $output);
    }

    public function testRenderScriptsWithEmptyScripts(): void
    {
        $this->assertSame('', $this->stub->renderScripts());
    }

    public function testRenderScriptsRendersScriptTags(): void
    {
        $this->stub->setConfig('scripts', ['BootstrapTools./js/app']);

        $output = $this->stub->renderScripts(['block' => false]);

        $this->assertStringContainsString('script', $output);
        $this->assertStringContainsString('js/app', $output);
    }

    public function testRenderWithConfigureOverride(): void
    {
        Configure::write('Bootstrap', ['appName' => 'Configured Name']);
        $this->stub->themeSettingsInitialize([]);

        $this->assertSame('Configured Name', $this->stub->get('appName'));
    }
}
