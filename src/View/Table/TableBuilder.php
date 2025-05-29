<?php
namespace BootstrapTools\View\Table;

use Cake\Core\InstanceConfigTrait;

class TableBuilder implements TableBuilderInterface
{
    use InstanceConfigTrait;
    /**
     * Default options for the table builder.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'class' => 'table',
        'element' => 'BootstrapTools.table/default',
    ];

    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    public function setOptions(array $options): self
    {
        $this->setConfig($options);

        return $this;
    }

    public function getOptions(): array
    {
        return $this->getConfig();
    }

    public function data(iterable $data): self
    {
        $this->setConfig('data', $data);

        return $this;
    }

    public function columns(array $columns): self
    {
        $this->setConfig('columns', $columns);

        return $this;
    }

    public function rowActions(array $rowActions): self
    {
        $this->setConfig('rowActions', $rowActions);

        return $this;
    }

    public function pagination(bool $enabled = true): self
    {
        $this->setConfig('pagination', $enabled);

        return $this;
    }

    public function tableActions(array $tableActions): self
    {
        $this->setConfig('tableActions', $tableActions);

        return $this;
    }    
}