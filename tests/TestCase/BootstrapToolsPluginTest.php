<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase;

use BootstrapTools\BootstrapToolsPlugin;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;
use Cake\Core\ContainerInterface;
use Cake\Core\PluginApplicationInterface;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * BootstrapToolsPluginTest
 */
class BootstrapToolsPluginTest extends TestCase
{
    public function testPluginIsInstanceOfBasePlugin(): void
    {
        $this->assertInstanceOf(BasePlugin::class, new BootstrapToolsPlugin());
    }

    public function testBootstrapIsNoop(): void
    {
        $plugin = new BootstrapToolsPlugin();

        $this->assertSame(null, $plugin->bootstrap($this->createMock(PluginApplicationInterface::class)));
    }

    public function testRoutesConnectPluginScope(): void
    {
        $plugin = new BootstrapToolsPlugin();
        $builder = Router::createRouteBuilder('/');
        $plugin->routes($builder);

        $url = Router::url([
            'plugin' => 'BootstrapTools',
            'controller' => 'Example',
            'action' => 'menu',
        ]);

        $this->assertStringContainsString('/bootstrap-tools', $url);
    }

    public function testMiddlewareReturnsSameQueue(): void
    {
        $plugin = new BootstrapToolsPlugin();
        $queue = new MiddlewareQueue();

        $this->assertSame($queue, $plugin->middleware($queue));
    }

    public function testConsoleReturnsCommandCollection(): void
    {
        $plugin = new BootstrapToolsPlugin();
        $commands = new CommandCollection();

        $result = $plugin->console($commands);

        $this->assertInstanceOf(CommandCollection::class, $result);
    }

    public function testServicesAcceptsContainer(): void
    {
        $plugin = new BootstrapToolsPlugin();
        $container = $this->createMock(ContainerInterface::class);

        $this->assertSame(null, $plugin->services($container));
    }
}
