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
use Symfony\Component\EventDispatcher\Event;

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
            'argv'  => [],
        ],
        'console.command.config.cache' => [
            'class' => '\\Speedwork\\Console\\Commands\\ConfigCacheCommand',
            'argv'  => ['app.files'],
        ],
        'console.command.config.clear' => [
            'class' => '\\Speedwork\\Console\\Commands\\ConfigClearCommand',
            'argv'  => ['app.files'],
        ],
        'console.command.key:generate' => [
            'class' => '\\Speedwork\\Console\\Commands\\KeyGenerateCommand',
            'argv'  => [],
        ],
        'console.command.env' => [
            'class' => '\\Speedwork\\Console\\Commands\\EnvironmentCommand',
            'argv'  => [],
        ],
    ];

    public function register(Container $app)
    {
        $app['console'] = function ($app) {
            $console = new Console($app);

            $app['events']->dispatch('console.init.event', new ConsoleEvent($console));

            return $console;
        };

        $app['console.register'] = $app->protect(function ($commands) {
            $this->registerCommands($commands);
        });

        $this->registerCommands($this->commands);
    }

    /**
     * Register the given commands.
     *
     * @param array $commands
     */
    public function registerCommands(array $commands)
    {
        foreach ($commands as $key => $command) {
            $this->app[$key] = function ($app) use ($command) {
                $class = new ReflectionClass($command['class']);
                if (empty($command['argv'])) {
                    return $class->newInstance();
                } else {
                    return $class->newInstanceArgs($this->parseArgs($command['argv'], $app));
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
}
