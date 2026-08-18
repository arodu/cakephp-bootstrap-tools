<?php
declare(strict_types=1);

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

    /**
     * Constructor.
     *
     * @param array $config Initial configuration options.
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * Set multiple options at once.
     *
     * @param array $options Table configuration.
     * @return $this
     */
    public function setOptions(array $options): self
    {
        $this->setConfig($options);

        return $this;
    }

    /**
     * Get the current configuration.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->getConfig();
    }

    /**
     * Set the table data.
     *
     * @param iterable $data Rows to render.
     * @return $this
     */
    public function data(iterable $data): self
    {
        $this->setConfig('data', $data);

        return $this;
    }

    /**
     * Set the table columns.
     *
     * @param array $columns Column definitions.
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->setConfig('columns', $columns);

        return $this;
    }

    /**
     * Set the row actions.
     *
     * @param array $rowActions Row actions definition.
     * @return $this
     */
    public function rowActions(array $rowActions): self
    {
        $this->setConfig('rowActions', $rowActions);

        return $this;
    }

    /**
     * Enable or disable pagination.
     *
     * @param bool $enabled Whether pagination is enabled.
     * @return $this
     */
    public function pagination(bool $enabled = true): self
    {
        $this->setConfig('pagination', $enabled);

        return $this;
    }

    /**
     * Set the table actions.
     *
     * @param array $tableActions Table actions definition.
     * @return $this
     */
    public function tableActions(array $tableActions): self
    {
        $this->setConfig('tableActions', $tableActions);

        return $this;
    }
}
