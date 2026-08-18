<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\View\Trait;

use BootstrapTools\View\Trait\MenuLoaderTrait;

/**
 * Stub class consuming MenuLoaderTrait for testing.
 */
class MenuLoaderTraitStub
{
    use MenuLoaderTrait;

    /**
     * Captured loaded helpers.
     *
     * @var array<string, array>
     */
    public array $loadedHelpers = [];

    /**
     * Stub of View::loadHelper capturing arguments.
     *
     * @param string $name
     * @param array $config
     * @return void
     */
    public function loadHelper(string $name, array $config = []): void
    {
        $this->loadedHelpers[$name] = $config;
    }

    /**
     * Exposes the protected options builder.
     *
     * @param string $key
     * @param array $options
     * @return array
     */
    public function buildOptions(string $key, array $options): array
    {
        return $this->buildMenuOptions($key, $options);
    }
}
