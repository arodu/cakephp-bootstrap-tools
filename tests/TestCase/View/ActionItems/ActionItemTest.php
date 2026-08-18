<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\ActionItems;

use BootstrapTools\View\ActionItems\ActionItem;
use BootstrapTools\View\ActionItems\ActionType;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use ReflectionProperty;

/**
 * ActionItemTest
 */
class ActionItemTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->clearRegistry();
    }

    public function tearDown(): void
    {
        $this->clearRegistry();
        parent::tearDown();
    }

    private function clearRegistry(): void
    {
        $prop = new ReflectionProperty(ActionItem::class, 'registry');
        $prop->setAccessible(true);
        $prop->setValue(null, []);
    }

    public function testSetAndGetRegisteredItem(): void
    {
        ActionItem::set('custom', [
            'type' => ActionType::Link,
            'url' => '/custom',
            'label' => 'Custom',
        ]);

        $item = ActionItem::get('custom');

        $this->assertInstanceOf(ActionItem::class, $item);
        $this->assertSame('Custom', $item->toArray()['label']);
        $this->assertSame('/custom', $item->toArray()['url']);
        $this->assertSame(ActionType::Link, $item->toArray()['type']);
    }

    public function testSetMergesWithPreviousOptions(): void
    {
        ActionItem::set('custom', [
            'type' => ActionType::Link,
            'url' => '/custom',
            'label' => 'First',
        ]);
        ActionItem::set('custom', ['label' => 'Second']);

        $item = ActionItem::get('custom');
        $this->assertSame('Second', $item->toArray()['label']);
        $this->assertSame('/custom', $item->toArray()['url']);
    }

    public function testSetWithoutActionTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be an instance');

        ActionItem::set('bad', ['label' => 'No type']);
    }

    public function testSetWithStringTypeThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ActionItem::set('bad', ['type' => 'link']);
    }

    public function testGetDefaults(): void
    {
        $cases = [
            [ActionItem::INDEX, ActionType::Link],
            [ActionItem::VIEW, ActionType::Link],
            [ActionItem::ADD, ActionType::Link],
            [ActionItem::EDIT, ActionType::Link],
            [ActionItem::DELETE, ActionType::PostLink],
            [ActionItem::CANCEL, ActionType::Link],
            [ActionItem::OPEN_MODAL, ActionType::ModalLink],
            [ActionItem::CLOSE_MODAL, ActionType::Link],
            [ActionItem::LIMIT_CONTROL, ActionType::LimitControl],
            [ActionItem::SUBMIT, ActionType::Button],
            [ActionItem::BUTTON, ActionType::Button],
            [ActionItem::RESET, ActionType::Button],
            [ActionItem::AJAX_SUBMIT, ActionType::Button],
        ];

        foreach ($cases as [$name, $type]) {
            $item = ActionItem::get($name);
            $this->assertSame($type, $item->toArray()['type'], "Default type for {$name}");
        }
    }

    public function testGetDefaultLimitControlOptions(): void
    {
        $item = ActionItem::get(ActionItem::LIMIT_CONTROL);

        $this->assertSame([], $item->toArray()['limits']);
        $this->assertSame('mb-0', $item->toArray()['options']['spacing']);
    }

    public function testGetUnknownItemThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not registred');

        ActionItem::get('nope');
    }

    public function testWithOptionsMerges(): void
    {
        $item = ActionItem::get(ActionItem::ADD)->withOptions(['label' => 'Create', 'class' => 'btn-sm']);

        $data = $item->toArray();
        $this->assertSame('Create', $data['label']);
        $this->assertSame('btn-sm', $data['class']);
        $this->assertSame('success', $data['color']);
    }

    public function testWithOptionsReturnsSameObject(): void
    {
        $item = ActionItem::get(ActionItem::ADD);
        $result = $item->withOptions(['label' => 'X']);

        $this->assertSame($item, $result);
    }

    public function testToArrayReturnsOptions(): void
    {
        $options = [
            'type' => ActionType::Link,
            'url' => '/a',
            'label' => 'A',
        ];
        ActionItem::set('t', $options);

        $this->assertSame($options, ActionItem::get('t')->toArray());
    }
}
