<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Console\Commands;

use ClassPreloader\Exceptions\VisitorExceptionInterface;
use ClassPreloader\Factory;
use Speedwork\Console\Command;
use Speedwork\Console\Util\Composer;
use Speedwork\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class OptimizeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize the framework for better performance';

    /**
     * The composer instance.
     *
     * @var \Speedwork\Console\Util\Composer
     */
    protected $composer;

    /**
     * The filesystem instance.
     *
     * @var \Speedwork\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new optimize command instance.
     *
     * @param \Speedwork\Console\Util\Composer $composer
     * @param \Speedwork\Filesystem\Filesystem $files
     */
    public function __construct(Composer $composer, Filesystem $files)
    {
        parent::__construct();

        $this->composer = $composer;
        $this->files    = $files;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $this->info('Generating optimized class loader');

        if ($this->option('psr')) {
            $this->composer->dumpAutoloads();
        } else {
            $this->composer->dumpOptimized();
        }

        if ($this->option('force') || $this->app['config']['app.debug'] !== true) {
            $this->info('Compiling common classes');
            $this->compileClasses();
        } else {
            $this->call('clear-compiled');
        }
    }

    /**
     * Generate the compiled class file.
     */
    protected function compileClasses()
    {
        $preloader = (new Factory())->create(['skip' => true]);

        $handle = $preloader->prepareOutput($this->app->getPath('cache').'compiled.php');

        foreach ($this->getClassFiles() as $file) {
            try {
                fwrite($handle, $preloader->getCode($file, false)."\n");
            } catch (VisitorExceptionInterface $e) {
            }
        }

        fclose($handle);
    }

    /**
     * Get the classes that should be combined and compiled.
     *
     * @return array
     */
    protected function getClassFiles()
    {
        $app = $this->app;

        $files = $this->getCompilePaths();
        $files = array_merge($files, $app['config']->get('compile.files', []));

        foreach ($app['config']->get('compile.providers', []) as $provider) {
            $files = array_merge($files, forward_static_call([$provider, 'compiles']));
        }

        $paths = $this->app->getPath();

        foreach ($files as &$file) {
            foreach ($paths as $key => $path) {
                $file = str_replace('{'.$key.'}', $path, $file);
            }
        }

        $files = array_map('realpath', $files);

        foreach ($files as $key => $file) {
            if (!$app['files']->exists($file)) {
                unset($files[$key]);
            }
        }

        return $files;
    }

    /**
     * Get the list of files from complile locations.
     *
     * @return array
     */
    protected function getCompilePaths()
    {
        $locations = $this->app['config']->get('compile.locations');

        $paths = $this->app->getPath();

        $files = [];

        foreach ($locations as &$location) {
            foreach ($paths as $key => $path) {
                $location = str_replace('{'.$key.'}', $path, $location);
            }

            $files = array_merge($files, $this->getCompileFiles($location));
        }

        return $files;
    }

    protected function getCompileFiles($location)
    {
        $files = [];
        if ($this->files->exists($location)) {
            $extension = $this->files->extension($location);
            if ($extension == 'php') {
                return include $location;
            }

            $paths = $this->files->get($location);
            $paths = explode("\n", $paths);

            foreach ($paths as $file) {
                if (!empty($file) && substr($file, 0, 1) !== '#') {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the compiled class file to be written.'],

            ['psr', null, InputOption::VALUE_NONE, 'Do not optimize Composer dump-autoload.'],
        ];
    }
}
