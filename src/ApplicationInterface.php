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

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
interface ApplicationInterface
{
    /**
     * Call a console application command.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return int
     */
    public function call($command, array $parameters = []);

    /**
     * Get the output from the last command.
     *
     * @return string
     */
    public function output();
}
