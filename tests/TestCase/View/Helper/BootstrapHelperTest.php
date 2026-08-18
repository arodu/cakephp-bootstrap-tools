<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Helper;

use BootstrapTools\Utility\Color;
use BootstrapTools\View\Helper\BootstrapHelper;
use BootstrapTools\View\VisualElement\VisualElement;
use Cake\TestSuite\TestCase;
use Cake\View\View;

/**
 * BootstrapHelperTest
 */
class BootstrapHelperTest extends TestCase
{
    private BootstrapHelper $Helper;

    public function setUp(): void
    {
        parent::setUp();
        $view = new View();
        $view->loadHelper('Html');
        $view->loadHelper('Url');
        $this->Helper = new BootstrapHelper($view);
    }

    public function testBadgeDefault(): void
    {
        $output = $this->Helper->badge(new VisualElement(1, 'Label'));

        $this->assertStringContainsString('text-bg-secondary', $output);
        $this->assertStringContainsString('class="circle-fill me-1"', $output);
        $this->assertStringContainsString('>Label</span>', $output);
    }

    public function testBadgeWithColorPillIconAndClass(): void
    {
        $output = $this->Helper->badge(
            new VisualElement(1, 'New', 'bi-star', 'success', 'A badge'),
            ['pill' => true, 'class' => 'extra', 'icon' => 'bi-plus'],
        );

        $this->assertStringContainsString('badge text-bg-success rounded-pill extra', $output);
        $this->assertStringContainsString('<i class="bi-plus me-1"></i>', $output);
        $this->assertStringContainsString('title="A badge"', $output);
        $this->assertStringContainsString('aria-label="New"', $output);
    }

    public function testBadgeWithTooltipOption(): void
    {
        $output = $this->Helper->badge(new VisualElement(1, 'Tip', description: 'The tooltip'), ['tooltip' => 'bottom']);

        $this->assertStringContainsString('data-bs-toggle="tooltip"', $output);
        $this->assertStringContainsString('data-bs-placement="bottom"', $output);
        $this->assertStringContainsString('data-bs-title="The tooltip"', $output);
    }

    public function testBadgeWithTooltipEnabledConfig(): void
    {
        $this->Helper->setConfig('defaults.tooltip.enabled', true);
        $output = $this->Helper->badge(new VisualElement(1, 'Tip'));

        $this->assertStringContainsString('data-bs-toggle="tooltip"', $output);
        $this->assertStringContainsString('data-bs-placement="top"', $output);
    }

    public function testBadgeWithIconDisabled(): void
    {
        $output = $this->Helper->badge(new VisualElement(1, 'X', 'bi-star'), ['icon' => false]);

        $this->assertStringNotContainsString('<i ', $output);
    }

    public function testBadgeWithVisualElementInterface(): void
    {
        $output = $this->Helper->badge(new VisualElementStub());

        $this->assertStringContainsString('text-bg-success', $output);
        $this->assertStringContainsString('>Stub</span>', $output);
    }

    public function testBadgeColorFromConfig(): void
    {
        $this->Helper->setConfig('badge.color', Color::DANGER);
        $output = $this->Helper->badge(new VisualElement(1, 'Danger'));

        $this->assertStringContainsString('text-bg-danger', $output);
    }

    public function testTextDefaultColor(): void
    {
        $output = $this->Helper->text(new VisualElement(1, 'Muted'));

        $this->assertStringContainsString('class="text-secondary', $output);
        $this->assertStringContainsString('>Muted</span>', $output);
    }

    public function testTextWithColorAndClass(): void
    {
        $output = $this->Helper->text(new VisualElement(1, 'Green', color: Color::SUCCESS), ['class' => 'fw-bold']);

        $this->assertStringContainsString('text-success fw-bold', $output);
    }

    public function testAlert(): void
    {
        $output = $this->Helper->alert(new VisualElement(1, 'Heads up', 'bi-info', 'info', 'Some content'));

        $this->assertStringContainsString('class="alert alert-info"', $output);
        $this->assertStringContainsString('role="alert"', $output);
        $this->assertStringContainsString('alert-heading', $output);
        $this->assertStringContainsString('Some content', $output);
    }

    public function testAlertDismissible(): void
    {
        $output = $this->Helper->alert(new VisualElement(1, 'Heads up'), ['dismissible' => true]);

        $this->assertStringContainsString('class="btn-close"', $output);
        $this->assertStringContainsString('data-bs-dismiss="alert"', $output);
    }

    public function testAlertWithoutDismissible(): void
    {
        $output = $this->Helper->alert(new VisualElement(1, 'Heads up'));

        $this->assertStringNotContainsString('btn-close', $output);
    }

    public function testIcon(): void
    {
        $output = $this->Helper->icon(new VisualElement(1, 'Search', 'bi-search', Color::PRIMARY));

        $this->assertStringContainsString('<i class="bi-search text-primary"></i>', $output);
    }

    public function testIconWithoutIconNameUsesConfigDefault(): void
    {
        $output = $this->Helper->icon(new VisualElement(1, 'X'));

        $this->assertStringContainsString('circle-fill', $output);
        $this->assertStringContainsString('text-secondary', $output);
    }

    public function testIconWithEmptyNameReturnsEmpty(): void
    {
        $this->Helper->setConfig('defaults.icon.name', null);

        $this->assertSame('', $this->Helper->icon(new VisualElement(1, 'X', null)));
    }

    public function testIconWithTooltipOption(): void
    {
        $output = $this->Helper->icon(new VisualElement(1, 'I'), ['tooltip' => 'end']);

        $this->assertStringContainsString('data-bs-placement="end"', $output);
    }

    public function testLinkUsesOptionsUrl(): void
    {
        $output = $this->Helper->link(new VisualElement(1, 'Go', 'bi-arrow-right', Color::PRIMARY), ['url' => '/go']);

        $this->assertStringContainsString('href="/go"', $output);
        $this->assertStringContainsString('class="text-decoration-none text-primary"', $output);
        $this->assertStringContainsString('>Go</a>', $output);
    }

    public function testLinkDefaultsToHashUrl(): void
    {
        $output = $this->Helper->link(new VisualElement(1, 'Go'));

        $this->assertStringContainsString('href="#"', $output);
        $this->assertStringContainsString('text-secondary', $output);
    }

    public function testButtonWithElementUrl(): void
    {
        $output = $this->Helper->button(new VisualElement(1, 'Save', 'bi-check', Color::SUCCESS, '', '/save'));

        $this->assertStringContainsString('href="/save"', $output);
        $this->assertStringContainsString('class="btn btn-success"', $output);
        $this->assertStringContainsString('>Save</a>', $output);
    }

    public function testButtonFallsBackToHashUrl(): void
    {
        $output = $this->Helper->button(new VisualElement(1, 'Save'));

        $this->assertStringContainsString('href="#"', $output);
    }

    public function testTagsWithStrings(): void
    {
        $output = $this->Helper->tags(['php', 'cake']);

        $this->assertStringContainsString('text-bg-secondary', $output);
        $this->assertStringContainsString('>php</span>', $output);
        $this->assertStringContainsString('>cake</span>', $output);
    }

    public function testTagsWithObjectsAndOptions(): void
    {
        $output = $this->Helper->tags(
            [new VisualElement(1, 'Tag A'), 'plain'],
            ['color' => 'primary', 'class' => 'tag-custom'],
        );

        $this->assertStringContainsString('text-bg-primary tag-custom', $output);
        $this->assertStringContainsString('>Tag A</span>', $output);
        $this->assertStringContainsString('>plain</span>', $output);
    }

    public function testListGroupWithLiItems(): void
    {
        $output = $this->Helper->listGroup(['One', 'Two']);

        $this->assertStringContainsString('<ul class="list-group ', $output);
        $this->assertStringContainsString('class="list-group-item', $output);
        $this->assertStringContainsString('>One</li>', $output);
    }

    public function testListGroupWithLinkItemsUsesDiv(): void
    {
        $output = $this->Helper->listGroup(
            [
                ['value' => 1, 'label' => 'Linked', 'url' => '/link'],
            ],
            ['itemOptions' => ['tag' => 'a']],
        );

        $this->assertStringContainsString('<div class="list-group ', $output);
        $this->assertStringContainsString('href="/link"', $output);
        $this->assertStringContainsString('list-group-item-action', $output);
    }

    public function testListGroupStyles(): void
    {
        $output = $this->Helper->listGroup(['x'], ['flush' => true, 'numbered' => true, 'horizontal' => 'sm', 'class' => 'lg']);

        $this->assertStringContainsString('list-group-flush', $output);
        $this->assertStringContainsString('list-group-numbered', $output);
        $this->assertStringContainsString('list-group-horizontal-sm', $output);
        $this->assertStringContainsString('lg', $output);
    }

    public function testListGroupItemActiveDisabled(): void
    {
        $output = $this->Helper->listGroupItem(
            new VisualElement(1, 'Item'),
            ['active' => true, 'disabled' => true],
        );

        $this->assertStringContainsString('list-group-item active disabled', $output);
        $this->assertStringContainsString('aria-current="true"', $output);
        $this->assertStringContainsString('aria-disabled="true"', $output);
    }

    public function testListGroupItemWithBadgeAndDescription(): void
    {
        $output = $this->Helper->listGroupItem(
            new VisualElement(1, 'Task', description: 'Details here'),
            ['badge' => new VisualElement(2, '5', color: Color::PRIMARY)],
        );

        $this->assertStringContainsString('badge text-bg-primary', $output);
        $this->assertStringContainsString('ms-auto', $output);
        $this->assertStringContainsString('class="d-block text-muted"', $output);
        $this->assertStringContainsString('>Details here</small>', $output);
    }

    public function testListGroupItemAsButton(): void
    {
        $output = $this->Helper->listGroupItem(new VisualElement(1, 'Btn'), ['tag' => 'button']);

        $this->assertStringContainsString('<button', $output);
        $this->assertStringContainsString('list-group-item-action', $output);
    }

    public function testDropdownItemLink(): void
    {
        $output = $this->Helper->dropdownItem(new VisualElement(1, 'Item', 'bi-1', null, null, '/item'));

        $this->assertStringContainsString('class="dropdown-item', $output);
        $this->assertStringContainsString('href="/item"', $output);
        $this->assertStringContainsString('>Item</a>', $output);
    }

    public function testDropdownItemButton(): void
    {
        $output = $this->Helper->dropdownItem(new VisualElement(1, 'Press'), ['type' => 'button']);

        $this->assertStringContainsString('<button type="button" class="dropdown-item', $output);
        $this->assertStringContainsString('>Press</button>', $output);
    }

    public function testDropdownItemHeader(): void
    {
        $output = $this->Helper->dropdownItem(new VisualElement(1, 'Section'), ['type' => 'header']);

        $this->assertStringContainsString('class="dropdown-header', $output);
        $this->assertStringContainsString('>Section</h6>', $output);
    }

    public function testDropdownItemDivider(): void
    {
        $output = $this->Helper->dropdownItem(new VisualElement(0, ''), ['type' => 'divider']);

        $this->assertStringContainsString('dropdown-divider', $output);
    }

    public function testDropdownItemInvalidTypeReturnsEmpty(): void
    {
        $this->assertSame('', $this->Helper->dropdownItem(new VisualElement(1, 'X'), ['type' => 'nope']));
    }

    public function testDropdown(): void
    {
        $output = $this->Helper->dropdown(
            new VisualElement(1, 'Menu', 'bi-menu', Color::PRIMARY),
            ['Item One', ['value' => 2, 'label' => 'Item Two', 'url' => '/two']],
            ['id' => 'menu-1', 'align' => 'end', 'menuClass' => 'my-menu'],
        );

        $this->assertStringContainsString('class="dropdown d-inline-block "', $output);
        $this->assertStringContainsString('id="menu-1"', $output);
        $this->assertStringContainsString('dropdown-toggle', $output);
        $this->assertStringContainsString('aria-expanded="false"', $output);
        $this->assertStringContainsString('dropdown-menu my-menu dropdown-menu-end', $output);
        $this->assertStringContainsString('>Item One</a>', $output);
        $this->assertStringContainsString('href="/two"', $output);
    }

    public function testDropdownWithDividersAndHeaders(): void
    {
        $output = $this->Helper->dropdown(
            new VisualElement(1, 'Menu'),
            [
                ['type' => 'header', 'element' => new VisualElement(0, 'Group')],
                ['type' => 'divider'],
                ['element' => new VisualElement(2, 'Item', url: '/item'), 'options' => []],
            ],
        );

        $this->assertStringContainsString('dropdown-header', $output);
        $this->assertStringContainsString('>Group</h6>', $output);
        $this->assertStringContainsString('dropdown-divider', $output);
        $this->assertStringContainsString('href="/item"', $output);
    }

    public function testDropdownWithDirection(): void
    {
        $output = $this->Helper->dropdown(new VisualElement(1, 'Menu'), [], ['direction' => 'up']);

        $this->assertStringContainsString('dropup', $output);
    }

    public function testDropdownButtonWithoutColorAddsDefaultClass(): void
    {
        $output = $this->Helper->dropdown(new VisualElement(1, 'Menu'), []);

        $this->assertStringContainsString('btn-secondary', $output);
    }

    public function testProgressBar(): void
    {
        $output = $this->Helper->progressBar(
            new VisualElement(50, 'Half', color: Color::SUCCESS),
            ['striped' => true, 'animated' => true, 'showLabel' => true],
        );

        $this->assertStringContainsString('role="progressbar"', $output);
        $this->assertStringContainsString('aria-valuenow="50"', $output);
        $this->assertStringContainsString('aria-valuemin="0"', $output);
        $this->assertStringContainsString('aria-valuemax="100"', $output);
        $this->assertStringContainsString('style="width: 50%;"', $output);
        $this->assertStringContainsString('bg-success progress-bar-striped progress-bar-animated', $output);
        $this->assertStringContainsString('>Half</div>', $output);
    }

    public function testProgressBarWithoutColor(): void
    {
        $output = $this->Helper->progressBar(new VisualElement(25, 'Quarter'));

        $this->assertStringContainsString('progress-bar ', $output);
        $this->assertStringNotContainsString('bg-', $output);
    }

    public function testProgressBarCustomMinMax(): void
    {
        $output = $this->Helper->progressBar(new VisualElement(5), ['min' => 0, 'max' => 10, 'wrapperClass' => 'wrapper']);

        $this->assertStringContainsString('style="width: 50%;"', $output);
        $this->assertStringContainsString('wrapper', $output);
    }

    public function testProgressBarWithInvalidRangeDoesNotDivideByZero(): void
    {
        $output = $this->Helper->progressBar(new VisualElement(10), ['min' => 5, 'max' => 5]);

        $this->assertStringContainsString('style="width: 0%;"', $output);
    }

    public function testSpinnerBorder(): void
    {
        $output = $this->Helper->spinner(new VisualElement(0, 'Loading', color: Color::PRIMARY));

        $this->assertStringContainsString('class="spinner-border text-primary "', $output);
        $this->assertStringContainsString('class="visually-hidden">Loading</span>', $output);
    }

    public function testSpinnerGrowSmall(): void
    {
        $output = $this->Helper->spinner(new VisualElement(0, 'Loading'), ['type' => 'grow', 'size' => 'sm']);

        $this->assertStringContainsString('spinner-grow', $output);
        $this->assertStringContainsString('spinner-grow-sm', $output);
    }

    public function testSpinnerDefaultLabel(): void
    {
        $output = $this->Helper->spinner(new VisualElement(0));

        $this->assertStringContainsString('Loading...', $output);
    }

    public function testToast(): void
    {
        $output = $this->Helper->toast(
            new VisualElement(1, 'Saved', 'bi-check', Color::SUCCESS, 'Your changes were saved'),
            ['id' => 'toast-1', 'timestamp' => 'just now'],
        );

        $this->assertStringContainsString('id="toast-1"', $output);
        $this->assertStringContainsString('data-bs-autohide="true"', $output);
        $this->assertStringContainsString('data-bs-delay="5000"', $output);
        $this->assertStringContainsString('bg-success', $output);
        $this->assertStringContainsString('class="btn-close', $output);
        $this->assertStringContainsString('Your changes were saved', $output);
    }

    public function testToastWithDarkColorAddsWhiteCloseButton(): void
    {
        $output = $this->Helper->toast(new VisualElement(1, 'Dark', color: Color::DARK));

        $this->assertStringContainsString('text-white', $output);
        $this->assertStringContainsString('btn-close-white', $output);
    }

    public function testToastNotDismissibleAndAutohideDisabled(): void
    {
        $output = $this->Helper->toast(
            new VisualElement(1, 'T'),
            ['dismissible' => false, 'autohide' => false],
        );

        $this->assertStringNotContainsString('btn-close', $output);
        $this->assertStringContainsString('data-bs-autohide="false"', $output);
        $this->assertStringNotContainsString('data-bs-delay', $output);
    }

    public function testToastCustomClasses(): void
    {
        $output = $this->Helper->toast(
            new VisualElement(1, 'T'),
            ['class' => 'custom-toast', 'headerClass' => 'custom-header', 'bodyClass' => 'custom-body'],
        );

        $this->assertStringContainsString('custom-toast', $output);
        $this->assertStringContainsString('custom-header', $output);
        $this->assertStringContainsString('custom-body', $output);
    }
}
