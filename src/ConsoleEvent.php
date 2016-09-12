<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Console;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class ConsoleEvent extends Event
{
    private $console;

    public function __construct(Application $console)
    {
        $this->console = $console;
    }

    public function getConsole()
    {
        return $this->console;
    }
}
