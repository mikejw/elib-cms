<?php

declare(strict_types=1);

namespace Empathy\ELib\Gen;

use Empathy\MVC\Util\ControllerGen;

class AdminDSection extends ControllerGen
{
    protected string $name = 'dsection';

    protected string $module = 'admin';

    protected string $parent = "\Empathy\ELib\DSection\Controller";
}
