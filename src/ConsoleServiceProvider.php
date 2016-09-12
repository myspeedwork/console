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

use ReflectionClass;
use Speedwork\Console\Application as Console;
use Speedwork\Container\Container;
use Speedwork\Container\ServiceProvider;

/**
 * Speedwork Console Service Provider.
 *
 * @author sankar <sanar.suda@gmail.com>
 */
class ConsoleServiceProvider extends ServiceProvider
{
    protected $commands = [
        'console.command.serve' => [
            'class' => '\\Speedwork\\Console\\Commands\\ServeCommand',
            'args'  => [],
        ],

        'console.command.config.cache' => [
            'class' => '\\Speedwork\\Console\\Commands\\ConfigCacheCommand',
            'args'  => ['app.files'],
        ],
        'console.command.config.clear' => [
            'class' => '\\Speedwork\\Console\\Commands\\ConfigClearCommand',
            'args'  => ['app.files'],
        ],
    ];

    public function register(Container $app)
    {
        $app['console'] = function ($app) {
            $console = new Console($app);

            $app['events']->dispatch('console.init.event', new ConsoleEvent($this));

            return $console;
        };

        $this->registerCommands($this->commands);
    }

    /**
     * Register the given commands.
     *
     * @param array $commands
     */
    protected function registerCommands(array $commands)
    {
        foreach ($commands as $key => $command) {
            $this->app[$key] = function ($app) use ($command) {
                $class = new ReflectionClass($command['class']);
                if (empty($command['args'])) {
                    return $class->newInstance();
                } else {
                    return $class->newInstanceArgs($this->parseArgs($command['args'], $app));
                }
            };
        }

        $this->commands(array_keys($commands));
    }

    protected function parseArgs($args, $app)
    {
        $newArgs = [];

        foreach ($args as $arg) {
            if (is_string($arg) && substr($arg, 0, 4) == 'app.') {
                $newArgs[] = $app[substr($arg, 4)];
            } else {
                $newArgs[] = $arg;
            }
        }

        return $newArgs;
    }

    /**
     * Register the package's custom Speedwork commands.
     *
     * @param array|mixed $commands
     */
    protected function commands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        // To register the commands with Speedwork, we will grab each of the arguments
        // passed into the method and listen for Speedwork "start" event which will
        // give us the Speedwork console instance which we will give commands to.
        $events = $this->app['events'];

        $events->addListener(
            'console.init.event', function ($event) use ($commands) {
                $event->getConsole()->resolveCommands($commands);
            }
        );
    }
}
