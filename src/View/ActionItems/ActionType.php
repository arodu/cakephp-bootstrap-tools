<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionItems;

enum ActionType
{
    case Link;
    case PostLink;
    case LimitControl;
    case Button;
}