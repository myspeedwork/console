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

use Closure;
use Exception;
use Speedwork\Console\Application as Console;
use Speedwork\Core\Application;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\EventDispatcher\Event;
use Throwable;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class Kernel implements KernelInterface
{
    /**
     * The application implementation.
     *
     * @var \Speedwork\Core\Application
     */
    protected $app;

    /**
     * The Console application instance.
     *
     * @var \Speedwork\Console\Application
     */
    protected $console;

    /**
     * The Speedwork commands provided by the application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Indicates if the Closure commands have been loaded.
     *
     * @var bool
     */
    protected $commandsLoaded = false;

    /**
     * Create a new console kernel instance.
     *
     * @param \Speedwork\Core\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Run the console application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    public function handle($input, $output = null)
    {
        try {
            $this->bootstrap();

            if (!$this->commandsLoaded) {
                $this->commands();

                $this->commandsLoaded = true;
            }

            return $this->getConsole()->run($input, $output);
        } catch (Exception $e) {
            $this->renderException($output, $e);

            return 1;
        } catch (Throwable $e) {
            $e = new FatalThrowableError($e);

            $this->renderException($output, $e);

            return 1;
        }
    }

    /**
     * Terminate the application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param int                                             $status
     */
    public function terminate($input, $status)
    {
        $this->app->terminate();
    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands()
    {
    }

    /**
     * Register a Closure based command with the application.
     *
     * @param string  $signature
     * @param Closure $callback
     *
     * @return \Speedwork\\Console\ClosureCommand
     */
    public function command($signature, Closure $callback)
    {
        $command = new ClosureCommand($signature, $callback);

        $this->app['events']->addListener('console.init.event', function (Event $event) use ($command) {
            $console = $event->getConsole();
            $console->add($command);
        });

        return $command;
    }

    /**
     * Register the given command with the console application.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     */
    public function registerCommand($command)
    {
        $this->getConsole()->add($command);
    }

    /**
     * Run an Artisan console command by name.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return int
     */
    public function call($command, array $parameters = [])
    {
        $this->bootstrap();

        return $this->getConsole()->call($command, $parameters);
    }

    /**
     * Get all of the commands registered with the console.
     *
     * @return array
     */
    public function all()
    {
        $this->bootstrap();

        return $this->getConsole()->all();
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        $this->bootstrap();

        return $this->getConsole()->output();
    }

    /**
     * Bootstrap the application for speedwork commands.
     */
    public function bootstrap()
    {
    }

    /**
     * Get the Artisan application instance.
     *
     * @return \Speedwork\Console\Application
     */
    protected function getConsole()
    {
        if (is_null($this->console)) {
            return $this->console = (new Console($this->app, $this->app->version()))
                ->resolveCommands($this->commands);
        }

        return $this->console;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception                                        $e
     */
    protected function renderException($output, Exception $e)
    {
        (new ConsoleApplication())->renderException($e, $output);
    }
}
