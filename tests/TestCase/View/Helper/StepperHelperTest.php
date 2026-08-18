<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\Helper\StepperHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use InvalidArgumentException;

/**
 * StepperHelperTest
 */
class StepperHelperTest extends TestCase
{
    private StepperHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $view->loadHelper('Html');
        $this->Helper = new StepperHelper($view);
    }

    public function testAddItemWithoutLabelThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide a label');

        $this->Helper->addItem(['url' => '/step1']);
    }

    public function testAddItemReturnsSelf(): void
    {
        $result = $this->Helper->addItem(['label' => 'Step 1', 'url' => '/step1']);

        $this->assertSame($this->Helper, $result);
    }

    public function testAddItems(): void
    {
        $result = $this->Helper->addItems([
            ['label' => 'Step 1', 'url' => '/step1'],
            ['label' => 'Step 2', 'url' => '/step2'],
        ]);

        $this->assertSame($this->Helper, $result);

        $output = $this->Helper->render();
        $this->assertStringContainsString('Step 1', $output);
        $this->assertStringContainsString('Step 2', $output);
    }

    public function testRenderItemCurrent(): void
    {
        $this->Helper->addItem(['label' => 'Step 1', 'url' => '/step1']);
        $this->Helper->addItem(['label' => 'Step 2', 'url' => '/step2']);
        $this->Helper->currentStep(2);

        $output = $this->Helper->render();

        $this->assertStringContainsString('class="stepper-item current"', $output);
        $this->assertStringContainsString('href="/step2"', $output);
    }

    public function testRenderItemCompletedAndDisabled(): void
    {
        $this->Helper->addItems([
            ['label' => 'Done', 'url' => '/done', 'completed' => true],
            ['label' => 'Locked', 'url' => '/locked', 'disabled' => true],
        ]);

        $output = $this->Helper->render();

        $this->assertStringContainsString('class="stepper-item completed"', $output);
        $this->assertStringContainsString('class="stepper-item disabled"', $output);
        $this->assertStringContainsString('href="#', $output);
    }

    public function testRenderShowsIconWhenProvided(): void
    {
        $this->Helper->addItem(['label' => 'Step', 'url' => '/step', 'icon' => 'bi bi-check']);

        $output = $this->Helper->render();

        $this->assertStringContainsString('<i class="bi bi-check"></i>', $output);
    }

    public function testRenderFallsBackToIndexNumber(): void
    {
        $this->Helper->addItem(['label' => 'Step', 'url' => '/step']);

        $output = $this->Helper->render();

        $this->assertStringContainsString('stepper-indicator">1</div>', $output);
    }

    public function testRenderIncludesCssAndResets(): void
    {
        $this->Helper->addItem(['label' => 'Step', 'url' => '/step']);

        $output = $this->Helper->render();

        $this->assertStringContainsString('stepper-container', $output);

        // El CSS con block=true va al bloque del view, no al HTML de retorno.
        $css = $this->Helper->getView()->fetch('css');
        $this->assertStringContainsString('bst-style.min', $css);

        // reset() dejó el helper vacío.
        $second = $this->Helper->render();
        $this->assertStringNotContainsString('>Step</div>', $second);
    }

    public function testCssMethodRendersLink(): void
    {
        $this->Helper->addItem(['label' => 'Step', 'url' => '/step']);

        $css = $this->Helper->css(['block' => false]);

        $this->assertStringContainsString('rel="stylesheet"', $css);
        $this->assertStringContainsString('bst-style.min', $css);
    }

    public function testResetReturnsSelf(): void
    {
        $this->Helper->addItem(['label' => 'A', 'url' => '/a']);
        $result = $this->Helper->reset();

        $this->assertSame($this->Helper, $result);
    }
}
