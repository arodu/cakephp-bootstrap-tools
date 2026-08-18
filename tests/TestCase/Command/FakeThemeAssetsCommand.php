<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Command;

use BootstrapTools\Command\UpdateThemeAssetsCommand;
use Cake\Http\Client;

/**
 * Command subclass injecting a fake HTTP client.
 */
class FakeThemeAssetsCommand extends UpdateThemeAssetsCommand
{
    public string $zipContent = '';

    protected function createHttpClient(): Client
    {
        return new FakeClient($this->zipContent);
    }
}
