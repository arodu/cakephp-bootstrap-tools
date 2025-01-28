<?php
declare(strict_types=1);

namespace BootstrapTools\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\NotFoundException;

class ExampleController extends AppController
{

    public function initialize(): void
    {
        if (!Configure::read('debug')) {
            throw new NotFoundException();
        }
    }


    public function menu()
    {
    }

    public function stepper(int $index = 1)
    {
        $this->set(compact('index'));
    }
}
