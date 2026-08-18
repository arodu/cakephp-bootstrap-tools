<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Table;

use ArrayIterator;
use BootstrapTools\View\Table\TableBuilder;
use Cake\TestSuite\TestCase;

/**
 * TableBuilderTest
 */
class TableBuilderTest extends TestCase
{
    public function testConstructorWithConfig(): void
    {
        $builder = new TableBuilder(['class' => 'table table-striped']);

        $this->assertSame('table table-striped', $builder->getOptions()['class']);
    }

    public function testDefaults(): void
    {
        $builder = new TableBuilder();

        $this->assertSame('table', $builder->getOptions()['class']);
        $this->assertSame('BootstrapTools.table/default', $builder->getOptions()['element']);
    }

    public function testSetOptionsMergesAndReturnsSelf(): void
    {
        $builder = new TableBuilder();
        $result = $builder->setOptions(['class' => 'my-table', 'header' => false]);

        $this->assertSame($builder, $result);
        $this->assertSame('my-table', $builder->getOptions()['class']);
        $this->assertFalse($builder->getOptions()['header']);
    }

    public function testData(): void
    {
        $data = [['id' => 1], ['id' => 2]];
        $builder = new TableBuilder();
        $result = $builder->data($data);

        $this->assertSame($builder, $result);
        $this->assertSame($data, $builder->getOptions()['data']);
    }

    public function testDataAcceptsTraversableBackedUpByIterator(): void
    {
        $iterator = new ArrayIterator(['a' => 1]);
        $builder = new TableBuilder()->data($iterator);

        $this->assertSame($iterator, $builder->getOptions()['data']);
    }

    public function testColumns(): void
    {
        $columns = [['label' => 'Id'], ['label' => 'Name']];
        $builder = new TableBuilder();
        $result = $builder->columns($columns);

        $this->assertSame($builder, $result);
        $this->assertSame($columns, $builder->getOptions()['columns']);
    }

    public function testRowActions(): void
    {
        $rowActions = ['label' => 'Actions', 'formatter' => fn() => '<a>x</a>'];
        $builder = new TableBuilder();
        $result = $builder->rowActions($rowActions);

        $this->assertSame($builder, $result);
        $this->assertSame('Actions', $builder->getOptions()['rowActions']['label']);
    }

    public function testPaginationDefaultsToTrue(): void
    {
        $builder = new TableBuilder()->pagination();

        $this->assertTrue($builder->getOptions()['pagination']);
    }

    public function testPaginationDisabled(): void
    {
        $builder = new TableBuilder()->pagination(false);

        $this->assertFalse($builder->getOptions()['pagination']);
    }

    public function testTableActions(): void
    {
        $actions = ['formatter' => fn() => '<a>New</a>'];
        $builder = new TableBuilder();
        $result = $builder->tableActions($actions);

        $this->assertSame($builder, $result);
        $this->assertSame('<a>New</a>', $builder->getOptions()['tableActions']['formatter']());
    }

    public function testSetOptionsOverridesConstructorConfig(): void
    {
        $builder = new TableBuilder(['class' => 'original']);
        $builder->setOptions(['class' => 'overridden']);

        $this->assertSame('overridden', $builder->getOptions()['class']);
    }
}
