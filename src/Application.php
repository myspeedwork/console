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
use Speedwork\Container\Container;
use Speedwork\Util\Collection;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class Application extends SymfonyApplication implements ApplicationInterface
{
    /**
     * The Speedwork application instance.
     *
     * @var \Speedwork\Container\Container
     */
    protected $app;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;

    /**
     * Create a new Speedwork console application.
     *
     * @param \Speedwork\Container\Container $app
     * @param string                         $version
     */
    public function __construct(Container $app, $version = '1.0')
    {
        parent::__construct('Speedwork Framework', $version);

        $this->app = $app;
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $events = $app->get('events');
        $events->dispatch('console.init.event', new ConsoleEvent($this));
    }

    /**
     * Run an Speedwork console command by name.
     *
     * @param string $command
     * @param array  $parameters
     *
     * @return int
     */
    public function call($command, array $parameters = [])
    {
        $parameters = (new Collection($parameters))->prepend($command);

        $this->lastOutput = new BufferedOutput();

        $this->setCatchExceptions(false);

        $result = $this->run(new ArrayInput($parameters->toArray()), $this->lastOutput);

        $this->setCatchExceptions(true);

        return $result;
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput ? $this->lastOutput->fetch() : '';
    }

    /**
     * Add a command to the console.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    public function add(SymfonyCommand $command)
    {
        if ($command instanceof Command) {
            $command->setContainer($this->app);
        }

        return $this->addToParent($command);
    }

    /**
     * Add the command to the parent instance.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    protected function addToParent(SymfonyCommand $command)
    {
        return parent::add($command);
    }

    /**
     * Add a command, resolving through the application.
     *
     * @param string $command
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    public function resolve($command)
    {
        if (is_string($command)) {
            if (strpos($command, '\\') !== false) {
                $command = $this->getContainer()->make($command);
            } else {
                $command = $this->getContainer()->get($command);
            }
        } elseif (is_array($command)) {
            $command = $this->resolveCommand($command);
        }

        return $this->add($command);
    }

    /**
     * Register the given command.
     *
     * @param array $command
     */
    public function resolveCommand($command)
    {
        $class = new ReflectionClass($command['class']);
        if (empty($command['argv'])) {
            return $class->newInstance();
        } else {
            return $class->newInstanceArgs($this->parseArgs($command['argv']));
        }
    }

    /**
     * Parse arguments from command array.
     *
     * @param array $args Arguments
     *
     * @return array Formated arguments
     */
    protected function parseArgs($args)
    {
        $newArgs = [];

        foreach ($args as $arg) {
            if (is_string($arg) && substr($arg, 0, 4) == 'app.') {
                $newArgs[] = $this->getContainer()->get(substr($arg, 4));
            } elseif (is_string($arg) && strpos($arg, '\\') !== false) {
                $newArgs[] = new $arg();
            } else {
                $newArgs[] = $arg;
            }
        }

        return $newArgs;
    }

    /**
     * Resolve an array of commands through the application.
     *
     * @param array|mixed $commands
     *
     * @return $this
     */
    public function resolveCommands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
    }

    /**
     * Get the default input definitions for the applications.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption($this->getEnvironmentOption());

        return $definition;
    }

    /**
     * Get the global environment option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption()
    {
        $message = 'The environment the command should run under.';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Get the Container application instance.
     *
     * @return \Speedwork\Container\Container
     */
    public function getContainer()
    {
        return $this->app;
    }
}
