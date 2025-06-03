<?php
declare(strict_types=1);

namespace BootstrapTools\View\Helper;

use BootstrapTools\View\Table\TableBuilder;
use BootstrapTools\View\Table\TableBuilderInterface;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * Table helper
 */
class TableHelper extends Helper
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'builder' => TableBuilder::class,
        'element' => 'BootstrapTools.table/default',
        'class' => 'table',
    ];

    public function create(array $options = []): TableBuilderInterface
    {
        $config = Hash::merge($this->getConfig(), $options);
        $builderClass = $config['builder'];

        if (!is_subclass_of($builderClass, TableBuilderInterface::class)) {
            throw new \InvalidArgumentException(sprintf(
                'The builder class "%s" must extend "%s".',
                $builderClass,
                TableBuilderInterface::class
            ));
        }

        return new $builderClass($config);
    }

    public function render(TableBuilderInterface $builder, array $options = []): string
    {
        $config = Hash::merge($this->getConfig(), $builder->getOptions(), $options);
        $element = $config['element'];
        unset($config['element']);

        return $this->getView()->element($element, $config);
    }
}
