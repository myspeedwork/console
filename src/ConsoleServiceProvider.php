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

use Speedwork\Console\Application as Console;
use Speedwork\Console\Util\Composer;
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
        \Speedwork\Console\Commands\ServeCommand::class,
        \Speedwork\Console\Commands\KeyGenerateCommand::class,
        \Speedwork\Console\Commands\EnvironmentCommand::class,
        \Speedwork\Console\Commands\ClearCompiledCommand::class,
        \Speedwork\Console\Commands\OfflineCommand::class,
        \Speedwork\Console\Commands\ConfigClearCommand::class,
        \Speedwork\Console\Commands\ConfigCacheCommand::class,
        \Speedwork\Console\Commands\PublishCommand::class,
        'console.command.optimize' => [
            'class' => '\\Speedwork\\Console\\Commands\\OptimizeCommand',
            'argv'  => ['app.composer', 'app.files'],
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
            $this->commands($commands);
        });

        $app['composer'] = function ($app) {
            return new Composer($app['files']);
        };

        $this->commands($this->commands);
    }
}
