<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\Helper\ModalAjaxHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * ModalAjaxHelperTest
 */
class ModalAjaxHelperTest extends TestCase
{
    private ModalAjaxHelper $Helper;

    private View $view;

    public function setUp(): void
    {
        parent::setUp();
        $this->view = new View();
        $this->view->loadHelper('Html');
        $this->view->loadHelper('Url');
        $this->Helper = new ModalAjaxHelper($this->view);
    }

    public function testLinkBuildsAnchorAndRegistersTarget(): void
    {
        $output = $this->Helper->link('Open', '/open-modal');

        $this->assertStringContainsString('class="ajax-modal"', $output);
        $this->assertStringContainsString('data-bs-toggle="modal"', $output);
        $this->assertStringContainsString('data-bs-target="#ajax-modal"', $output);
        $this->assertStringContainsString('data-url="/open-modal"', $output);
        $this->assertStringContainsString('href="#', $output);
        $this->assertStringContainsString('>Open</a>', $output);
    }

    public function testLinkWithCustomTargetAndModalOptions(): void
    {
        $output = $this->Helper->link('Open', '/x', [
            'target' => 'custom-modal',
            'modalOptions' => ['size' => 'modal-lg'],
        ]);

        $this->assertStringContainsString('data-bs-target="#custom-modal"', $output);
        $this->assertStringContainsString('data-modal-options=', $output);

        $rendered = $this->Helper->render();
        $this->assertStringContainsString('id="custom-modal"', $rendered);
    }

    public function testGetTargetDefault(): void
    {
        $this->assertSame('ajax-modal', $this->Helper->getTarget());
    }

    public function testGetTargetWithConfig(): void
    {
        $this->Helper->setConfig('target', 'other');
        $this->assertSame('other', $this->Helper->getTarget());
    }

    public function testRenderRendersOneModalPerRegisteredTarget(): void
    {
        $this->Helper->link('A', '/a', ['target' => 'm1']);
        $this->Helper->link('B', '/b', ['target' => 'm2']);

        $output = $this->Helper->render();

        $this->assertStringContainsString('id="m1"', $output);
        $this->assertStringContainsString('id="m2"', $output);
        $this->assertStringContainsString('class="modal fade', $output);
    }

    public function testRenderWithNoTargetsIsEmpty(): void
    {
        $this->assertSame('', $this->Helper->render());
    }

    public function testRenderItemWithOptions(): void
    {
        $output = $this->Helper->renderItem('m1', [
            'title' => 'Hello',
            'modalOptions' => ['centered' => true, 'scrollable' => true, 'staticBackdrop' => true],
        ]);

        $this->assertStringContainsString('id="m1"', $output);
        $this->assertStringContainsString('modal-dialog-centered', $output);
        $this->assertStringContainsString('modal-dialog-scrollable', $output);
        $this->assertStringContainsString('backdrop', $output);
    }

    public function testRenderItemWithJsCallback(): void
    {
        $this->Helper->setConfig('jsCallback', 'function (event, detail) { console.log(detail); }');

        $output = $this->Helper->renderItem('m1');

        $this->assertStringContainsString('id="m1"', $output);
        $js = $this->view->fetch('script');
        $this->assertStringContainsString('console.log(detail)', $js);
        $this->assertStringContainsString('modalAjaxResponse', $js);
    }

    public function testRenderItemCloseOnSuccessConfig(): void
    {
        $this->Helper->setConfig('closeOnSuccess', true);
        $this->Helper->setConfig('reloadPageOnSuccess', true);
        $this->Helper->setConfig('reloadPageOnClose', true);

        $output = $this->Helper->renderItem('m1');
        $js = $this->view->fetch('script');

        $this->assertStringContainsString('id="m1"', $output);
        $this->assertStringContainsString('closeOnSuccess: true', $js);
        $this->assertStringContainsString('reloadPageOnSuccess: true', $js);
        $this->assertStringContainsString('reloadPageOnClose: true', $js);
    }

    public function testSetTitle(): void
    {
        $output = $this->Helper->setTitle('My Title');

        $this->assertStringContainsString('id="modal-title"', $output);
        $this->assertStringContainsString('class="d-none"', $output);
        $this->assertStringContainsString('>My Title</div>', $output);
    }
}
