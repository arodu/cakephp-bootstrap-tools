<?php

declare(strict_types=1);

namespace BootstrapTools\View\ActionElement;

interface ActionElementInterface
{
    public function getActionElement(array $options = []): ActionElement;
}
