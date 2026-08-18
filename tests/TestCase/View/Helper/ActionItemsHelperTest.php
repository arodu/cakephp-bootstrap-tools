<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\View\ActionItems\ActionItem;
use BootstrapTools\View\ActionItems\ActionType;
use BootstrapTools\View\Helper\ActionItemsHelper;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use ReflectionProperty;
use RuntimeException;
use stdClass;

/**
 * ActionItemsHelperTest
 */
class ActionItemsHelperTest extends TestCase
{
    private ActionItemsHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $this->clearRegistry();

        $view = new View();
        $view->loadHelper('Html');
        $view->loadHelper('Form');
        $view->loadHelper('Paginator');
        $view->loadHelper('Url');
        $view->loadHelper('BootstrapTools.ModalAjax');
        $this->Helper = new ActionItemsHelper($view);
        $this->Helper->getView()->Paginator->setPaginated(new FakePaginated());
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

    private function linkItem(string $name, string $url, string $label): ActionItem
    {
        return ActionItem::get($name)->withOptions(['url' => $url, 'label' => $label]);
    }

    public function testRegistryRegistersItem(): void
    {
        $result = $this->Helper->registry('published', [
            'type' => ActionType::Link,
            'url' => '/published',
            'label' => 'Published',
        ]);

        $this->assertSame($this->Helper, $result);
        $item = ActionItem::get('published');
        $this->assertSame('Published', $item->toArray()['label']);
    }

    public function testSetItemByRegistryNameThenRender(): void
    {
        $this->Helper->registry('published', [
            'type' => ActionType::Link,
            'url' => '/published',
            'label' => 'Published',
        ]);
        $this->Helper->setItem('published');

        $output = $this->Helper->render();

        $this->assertStringContainsString('/published', $output);
        $this->assertStringContainsString('>Published</a>', $output);
    }

    public function testSetItemWithInstance(): void
    {
        $item = $this->linkItem(ActionItem::ADD, '/add-custom', 'Add');
        $this->Helper->setItem($item);

        $output = $this->Helper->render();

        $this->assertStringContainsString('/add-custom', $output);
        $this->assertStringContainsString('>Add</a>', $output);
    }

    public function testSetItemWithOptions(): void
    {
        $this->Helper->setItem(ActionItem::INDEX, ['url' => '/list-all', 'label' => 'All']);

        $output = $this->Helper->render();

        $this->assertStringContainsString('/list-all', $output);
        $this->assertStringContainsString('>All</a>', $output);
    }

    public function testSetItemsWithStringKeys(): void
    {
        $this->Helper->setItems([
            'index' => ['url' => '/idx'],
            'add' => ['url' => '/new'],
        ]);

        $output = $this->Helper->render();

        $this->assertStringContainsString('/idx', $output);
        $this->assertStringContainsString('/new', $output);
    }

    public function testSetItemsWithInstancesUsesDefaultOptions(): void
    {
        $this->Helper->setItems([
            $this->linkItem(ActionItem::VIEW, '/view', 'View'),
            $this->linkItem(ActionItem::EDIT, '/edit', 'Edit'),
        ]);

        $output = $this->Helper->render();

        $this->assertStringContainsString('>View</a>', $output);
        $this->assertStringContainsString('>Edit</a>', $output);
    }

    public function testRenderResetsScopeByDefault(): void
    {
        $this->Helper->setItem(ActionItem::ADD, ['url' => '/add']);
        $this->Helper->render();

        $output = $this->Helper->render();
        $this->assertSame('', $output);
    }

    public function testRenderWithResetFalseKeepsItems(): void
    {
        $this->Helper->setItem(ActionItem::ADD, ['url' => '/add']);
        $this->Helper->render(['reset' => false]);

        $output = $this->Helper->render(['reset' => false]);
        $this->assertStringContainsString('>Add</a>', $output);
    }

    public function testRenderWithGroupWrapsInBtnGroup(): void
    {
        $this->Helper->setItem(ActionItem::INDEX, ['url' => '/index']);

        $output = $this->Helper->render(['group' => true]);

        $this->assertStringContainsString('class="btn-group"', $output);
        $this->assertStringContainsString('role="group"', $output);
    }

    public function testRenderWithGroupFromConfig(): void
    {
        $this->Helper->setConfig('defaultGroup', true);
        $this->Helper->setItem(ActionItem::INDEX, ['url' => '/index']);

        $output = $this->Helper->render();

        $this->assertStringContainsString('class="btn-group"', $output);
    }

    public function testRenderWithScopeKeepsSeparateItems(): void
    {
        $this->Helper->setItem(ActionItem::ADD, ['scope' => 'toolbar', 'url' => '/add']);
        $this->Helper->setItem(ActionItem::EDIT, ['url' => '/edit']);

        $toolbar = $this->Helper->render(['scope' => 'toolbar']);
        $default = $this->Helper->render();

        $this->assertStringContainsString('/add', $toolbar);
        $this->assertStringNotContainsString('/add', $default);
        $this->assertStringContainsString('/edit', $default);
    }

    public function testDropdownMethodRegistersScopeItem(): void
    {
        $result = $this->Helper->dropdown('group_actions', ['label' => 'Actions']);

        $this->assertSame($this->Helper, $result);

        $output = $this->Helper->render();
        $this->assertSame('group_actions', $output);
    }

    /**
     * @return array<string, array{ActionItem, string[]}>
     */
    public static function renderItemProvider(): array
    {
        return [
            'Link' => [
                ActionItem::get(ActionItem::INDEX)->withOptions(['url' => '/index']),
                ['<a ', 'href="/index"', '>List</a>'],
            ],
            'PostLink' => [
                ActionItem::get(ActionItem::DELETE)->withOptions(['url' => '/delete']),
                ['<form', 'method="post"', '/delete', '>Delete</a>'],
            ],
            'Button' => [
                ActionItem::get(ActionItem::SUBMIT)->withOptions(['url' => '/submit']),
                ['<button', 'type="submit"', 'Submit'],
            ],
            'ModalLink' => [
                ActionItem::get(ActionItem::OPEN_MODAL),
                ['<a ', 'data-bs-target', 'data-url', '>Open Modal</a>'],
            ],
        ];
    }

    /**
     * @dataProvider renderItemProvider
     */
    public function testRenderItemByType(ActionItem $item, array $expectedFragments): void
    {
        $output = $this->Helper->renderItem($item);

        foreach ($expectedFragments as $fragment) {
            $this->assertStringContainsString($fragment, $output, "Fragment: {$fragment}");
        }
    }

    public function testRenderItemLimitControlRendersSelect(): void
    {
        $item = ActionItem::get(ActionItem::LIMIT_CONTROL)->withOptions(['limits' => [10, 20, 50]]);

        $output = $this->Helper->renderItem($item);

        $this->assertStringContainsString('<select', $output);
        $this->assertStringContainsString('name="limit"', $output);
    }

    public function testRenderItemDropdownReturnsEmpty(): void
    {
        $item = ActionItem::get(ActionItem::ADD)->withOptions(['type' => ActionType::Dropdown]);

        $this->assertSame('', $this->Helper->renderItem($item));
    }

    public function testWithOptionsScopesOptions(): void
    {
        $this->Helper->withOptions(['scope' => 'toolbar', 'class' => 'btn-sm']);
        $this->Helper->setItem(ActionItem::INDEX, ['scope' => 'toolbar', 'url' => '/index']);

        $output = $this->Helper->render(['scope' => 'toolbar']);

        $this->assertStringContainsString('/index', $output);
    }

    public function testActionItemClassInvalidThrowsOnSetItem(): void
    {
        $this->Helper->setConfig('actionItemClass', stdClass::class);

        $this->expectException(RuntimeException::class);
        $this->Helper->setItem(ActionItem::ADD);
    }
}
