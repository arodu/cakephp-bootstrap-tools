<?php
declare(strict_types=1);

namespace BootstrapTools\View\ActionItems;

enum ActionType: string
{
    case Link = 'link';
    case PostLink = 'postLink';
    case LimitControl = 'limitControl';
    case Button = 'button';
}