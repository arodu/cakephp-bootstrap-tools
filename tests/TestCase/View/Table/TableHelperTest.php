<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Table;

use BootstrapTools\View\Helper\TableHelper;
use BootstrapTools\View\Table\TableBuilder;
use BootstrapTools\View\Table\TableBuilderInterface;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use InvalidArgumentException;
use stdClass;

/**
 * TableHelperTest
 */
class TableHelperTest extends TestCase
{
    private TableHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $view->loadHelper('Paginator');
        $this->Helper = new TableHelper($view);
    }

    public function testCreateReturnsBuilder(): void
    {
        $builder = $this->Helper->create();

        $this->assertInstanceOf(TableBuilderInterface::class, $builder);
        $this->assertInstanceOf(TableBuilder::class, $builder);
        $this->assertSame('table', $builder->getOptions()['class']);
    }

    public function testCreateMergesHelperConfigAndOptions(): void
    {
        $builder = $this->Helper->create(['class' => 'table-sm']);

        $this->assertSame('table-sm', $builder->getOptions()['class']);
        $this->assertSame('BootstrapTools.table/default', $builder->getOptions()['element']);
    }

    public function testCreateWithInvalidBuilderThrows(): void
    {
        $this->Helper->setConfig('builder', stdClass::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must extend');
        $this->Helper->create();
    }

    public function testRenderProducesTableRows(): void
    {
        $builder = (new TableBuilder())
            ->data([
                (object)['id' => 1, 'name' => 'Alice'],
                (object)['id' => 2, 'name' => 'Bob'],
            ])
            ->columns([
                'id' => ['label' => 'Id'],
                'name' => ['label' => 'Name'],
            ])
            ->rowActions([
                'label' => 'Actions',
                'formatter' => fn($item) => '<a href="/edit/' . $item->id . '">Edit</a>',
            ]);

        $output = $this->Helper->render($builder);

        $this->assertStringContainsString('<table class="table">', $output);
        $this->assertStringContainsString('<th>Id</th>', $output);
        $this->assertStringContainsString('<th class="actions">Actions</th>', $output);
        $this->assertStringContainsString('Alice', $output);
        $this->assertStringContainsString('<td class="actions">', $output);
        $this->assertStringContainsString('/edit/1', $output);
    }

    public function testRenderEmptyMessage(): void
    {
        $builder = (new TableBuilder())
            ->data([])
            ->columns(['id' => ['label' => 'Id']]);

        $output = $this->Helper->render($builder, ['emptyMessage' => 'Nothing here']);

        $this->assertStringContainsString('Nothing here', $output);
    }

    public function testRenderRespectsClassConfig(): void
    {
        $builder = (new TableBuilder())
            ->setOptions(['class' => 'table table-dark'])
            ->columns(['id' => ['label' => 'Id']])
            ->data([(object)['id' => 9]]);

        $output = $this->Helper->render($builder);

        $this->assertStringContainsString('class="table table-dark"', $output);
    }

    public function testRenderHeaderHidden(): void
    {
        $builder = (new TableBuilder())
            ->setOptions(['header' => false])
            ->columns(['id' => ['label' => 'Id']])
            ->data([(object)['id' => 9]]);

        $output = $this->Helper->render($builder);

        $this->assertStringNotContainsString('mb-3</h4>', $output);
        $this->assertStringContainsString('<table', $output);
    }
}
