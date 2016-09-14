<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Console\Util;

use Speedwork\Filesystem\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class Composer
{
    /**
     * The filesystem instance.
     *
     * @var \Speedwork\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The working path to regenerate from.
     *
     * @var string
     */
    protected $workingPath;

    /**
     * Create a new Composer manager instance.
     *
     * @param \Speedwork\Filesystem\Filesystem $files
     * @param string|null                      $workingPath
     */
    public function __construct(Filesystem $files, $workingPath = null)
    {
        $this->files       = $files;
        $this->workingPath = $workingPath;
    }

    /**
     * Regenerate the Composer autoloader files.
     *
     * @param string $extra
     */
    public function dumpAutoloads($extra = '')
    {
        $process = $this->getProcess();

        $process->setCommandLine(trim($this->findComposer().' dump-autoload '.$extra));

        $process->run();
    }

    /**
     * Regenerate the optimized Composer autoloader files.
     */
    public function dumpOptimized()
    {
        $this->dumpAutoloads('--optimize');
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        if (!$this->files->exists($this->workingPath.'/composer.phar')) {
            return 'composer';
        }

        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        return "{$binary} composer.phar";
    }

    /**
     * Get a new Symfony process instance.
     *
     * @return \Symfony\Component\Process\Process
     */
    protected function getProcess()
    {
        return (new Process('', $this->workingPath))->setTimeout(null);
    }

    /**
     * Set the working path used by the class.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setWorkingPath($path)
    {
        $this->workingPath = realpath($path);

        return $this;
    }
}
