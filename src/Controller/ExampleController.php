<?php
declare(strict_types=1);

namespace BootstrapTools\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

class ExampleController extends AppController
{
    /**
     * Only allow the example actions while in debug mode.
     *
     * @return void
     */
    public function initialize(): void
    {
        if (!Configure::read('debug')) {
            throw new NotFoundException();
        }
    }

    /**
     * Example menu action.
     *
     * @return void
     */
    public function menu(): void
    {
    }

    /**
     * Example stepper action.
     *
     * @param int $index
     * @return void
     */
    public function stepper(int $index = 1): void
    {
        $this->set(compact('index'));
    }
}
