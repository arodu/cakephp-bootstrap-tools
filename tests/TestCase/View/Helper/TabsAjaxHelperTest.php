<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\Helper\TabsAjaxHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * TabsAjaxHelperTest
 */
class TabsAjaxHelperTest extends TestCase
{
    private TabsAjaxHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $view->loadHelper('Html');
        $view->loadHelper('Url');
        $this->Helper = new TabsAjaxHelper($view);
    }

    public function testAddItemDefaultsLabelToKey(): void
    {
        $result = $this->Helper->addItem('profile');

        $this->assertSame($this->Helper, $result);

        $output = $this->Helper->renderNav();
        $this->assertStringContainsString('profile', $output);
    }

    public function testAddItemWithLabel(): void
    {
        $this->Helper->addItem('profile', ['label' => 'Profile', 'url' => '/profile']);

        $output = $this->Helper->renderNav();

        $this->assertStringContainsString('>Profile</a>', $output);
    }

    public function testRenderNav(): void
    {
        $this->Helper->addItem('tab1', ['label' => 'Tab 1']);
        $this->Helper->addItem('tab2', ['label' => 'Tab 2', 'active' => true]);

        $output = $this->Helper->renderNav();

        $this->assertStringContainsString('id="ajax-tabs"', $output);
        $this->assertStringContainsString('role="tablist"', $output);
        $this->assertStringContainsString('data-bs-toggle="tab"', $output);
        $this->assertStringContainsString('nav-link active', $output);
        $this->assertStringContainsString('href="#tab1"', $output);
    }

    public function testRenderNavWithDisabledTab(): void
    {
        $this->Helper->addItem('tab1', ['label' => 'Tab 1', 'disabled' => true]);

        $output = $this->Helper->renderNav();

        $this->assertStringContainsString('nav-link disabled', $output);
        $this->assertStringContainsString('aria-disabled="true"', $output);
    }

    public function testRenderNavWithLabelAsArray(): void
    {
        $this->Helper->addItem('tab1', [
            'label' => ['text' => 'With Icon', 'options' => ['class' => 'label-cls']],
        ]);

        $output = $this->Helper->renderNav();

        $this->assertStringContainsString('class="label-cls"', $output);
        $this->assertStringContainsString('>With Icon</span>', $output);
    }

    public function testRenderContent(): void
    {
        $this->Helper->addItem('tab1', ['label' => 'Tab 1', 'body' => 'Body content']);
        $this->Helper->addItem('tab2', ['label' => 'Tab 2', 'active' => true]);

        $output = $this->Helper->renderContent();

        $this->assertStringContainsString('id="ajax-tabs"', $output);
        $this->assertStringContainsString('class="tab-content"', $output);
        $this->assertStringContainsString('id="tab1"', $output);
        $this->assertStringContainsString('>Body content</div>', $output);
        $this->assertStringContainsString('show active', $output);
    }

    public function testRenderContentWithUrlAndLoadingBody(): void
    {
        $this->Helper->addItem('tab1', ['label' => 'Tab 1', 'url' => '/tab-data']);

        $output = $this->Helper->renderContent();

        $this->assertStringContainsString('data-url="/tab-data"', $output);
        $this->assertStringContainsString('Loading...', $output);
    }
}
