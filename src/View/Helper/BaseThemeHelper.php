<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use Cake\Event\EventInterface;
use Cake\View\Helper;
use Cake\View\View;

/**
 * BaseTheme helper
 * 
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
class BaseThemeHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected $_defaultConfig = [
        'meta' => [],
        'css' => [],
        'scripts' => [],
    ];

    public function beforeRender(EventInterface $event, $viewFile)
    {
        foreach ($this->getConfig('meta') ?? [] as $name => $content) {
            $this->getView()->Html->meta($name, $content, ['block' => true]);
        }
        $this->getView()->Html->css($this->getConfig('css') ?? [], ['block' => true]);
        $this->getView()->Html->script($this->getConfig('scripts') ?? [], ['block' => true]);
    }
}
