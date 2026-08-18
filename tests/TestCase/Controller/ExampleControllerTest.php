<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Controller;

use BootstrapTools\Controller\ExampleController;
use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;

/**
 * ExampleControllerTest (demo controller).
 */
class ExampleControllerTest extends TestCase
{
    private ServerRequest $request;

    public function setUp(): void
    {
        parent::setUp();
        Configure::write('debug', true);
        $this->request = new ServerRequest(['url' => '/bootstrap-tools/example/menu']);
    }

    public function tearDown(): void
    {
        Configure::write('debug', true);
        parent::tearDown();
    }

    public function testInitializeAllowedInDebug(): void
    {
        $controller = new ExampleController($this->request);

        $this->assertInstanceOf(ExampleController::class, $controller);
        $this->assertSame(null, $controller->initialize());
    }

    public function testInitializeThrowsWithoutDebug(): void
    {
        Configure::write('debug', false);

        $this->expectException(NotFoundException::class);
        new ExampleController($this->request);
    }

    public function testMenuAction(): void
    {
        $controller = new ExampleController($this->request);
        $controller->initialize();

        $this->assertSame(null, $controller->menu());
    }

    public function testStepperActionSetsIndex(): void
    {
        $controller = new ExampleController($this->request);
        $controller->initialize();
        $controller->stepper(3);

        $this->assertSame(3, $controller->viewBuilder()->getVar('index'));
    }

    public function testStepperActionDefaultIndex(): void
    {
        $controller = new ExampleController($this->request);
        $controller->initialize();
        $controller->stepper();

        $this->assertSame(1, $controller->viewBuilder()->getVar('index'));
    }
}
