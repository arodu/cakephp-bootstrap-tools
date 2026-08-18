<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Dropdown;

use BootstrapTools\View\Dropdown\DropdownBuilder;
use Cake\TestSuite\TestCase;

/**
 * DropdownBuilderTest
 */
class DropdownBuilderTest extends TestCase
{
    public function testDefaults(): void
    {
        $builder = new DropdownBuilder();

        $options = $builder->getOptions();
        $this->assertSame('Dropdown', $options['button']['text']);
        $this->assertSame('btn btn-primary dropdown-toggle', $options['button']['options']['class']);
        $this->assertFalse($options['split']);
        $this->assertSame('down', $options['direction']);
        $this->assertSame('dropdown-menu', $options['menu']['class']);
        $this->assertSame([], $options['menu']['items']);
        $this->assertSame('BootstrapTools.dropdown/default', $options['element']);
    }

    public function testConstructorWithConfig(): void
    {
        $builder = new DropdownBuilder(['direction' => 'up']);

        $this->assertSame('up', $builder->getOptions()['direction']);
    }

    public function testButtonSetsTextAndMergesOptions(): void
    {
        $builder = new DropdownBuilder();
        $result = $builder->button('Save', ['class' => 'btn btn-success']);

        $this->assertSame($builder, $result);
        $this->assertSame('Save', $builder->getOptions()['button']['text']);
        $this->assertSame('btn btn-success', $builder->getOptions()['button']['options']['class']);
    }

    public function testButtonWithoutOptionsKeepsDefaults(): void
    {
        $builder = new DropdownBuilder();
        $builder->button('Save');

        $this->assertSame('Save', $builder->getOptions()['button']['text']);
        $this->assertSame('dropdown', $builder->getOptions()['button']['options']['data-bs-toggle']);
    }

    public function testItems(): void
    {
        $items = [['text' => 'One', 'url' => '/one']];
        $builder = new DropdownBuilder();
        $result = $builder->items($items);

        $this->assertSame($builder, $result);
        $this->assertSame($items, $builder->getOptions()['menu']['items']);
    }

    public function testAddItemAppends(): void
    {
        $builder = new DropdownBuilder();
        $builder->items([['text' => 'First', 'url' => '/first']]);
        $builder->addItem('Second', '/second', ['class' => 'extra']);

        $items = $builder->getOptions()['menu']['items'];
        $this->assertCount(2, $items);
        $this->assertSame('Second', $items[1]['text']);
        $this->assertSame('/second', $items[1]['url']);
        $this->assertSame(['class' => 'extra'], $items[1]['options']);
    }

    public function testAddItemWithoutUrl(): void
    {
        $builder = new DropdownBuilder();
        $builder->addItem('Plain');

        $items = $builder->getOptions()['menu']['items'];
        $this->assertNull($items[0]['url']);
    }

    public function testDirectionReturnsSelf(): void
    {
        $builder = new DropdownBuilder();
        $result = $builder->direction('end');

        $this->assertSame($builder, $result);
        $this->assertSame('end', $builder->getOptions()['direction']);
    }

    public function testSplit(): void
    {
        $builder = new DropdownBuilder();
        $result = $builder->split();

        $this->assertSame($builder, $result);
        $this->assertTrue($builder->getOptions()['split']);
    }

    public function testSplitDisabled(): void
    {
        $builder = new DropdownBuilder()->split(false);

        $this->assertFalse($builder->getOptions()['split']);
    }

    public function testMenuOptionsMerges(): void
    {
        $builder = new DropdownBuilder();
        $builder->menuOptions(['class' => 'my-menu']);

        $this->assertSame('my-menu', $builder->getOptions()['menu']['class']);
        $this->assertSame([], $builder->getOptions()['menu']['items']);
    }

    public function testAddDivider(): void
    {
        $builder = new DropdownBuilder();
        $builder->items([['text' => 'Item', 'url' => '#']]);
        $builder->addDivider();

        $items = $builder->getOptions()['menu']['items'];
        $this->assertSame(['divider' => true], $items[1]);
    }

    public function testAddHeader(): void
    {
        $builder = new DropdownBuilder();
        $builder->addHeader('Group 1');

        $items = $builder->getOptions()['menu']['items'];
        $this->assertSame(['header' => 'Group 1'], $items[0]);
    }

    public function testSetOptionsReturnsSelf(): void
    {
        $builder = new DropdownBuilder();
        $result = $builder->setOptions(['split' => true]);

        $this->assertSame($builder, $result);
    }
}
