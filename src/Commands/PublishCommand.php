<?php

namespace Speedwork\Console\Commands;

use Speedwork\Console\Command;
use Speedwork\Container\ServiceProvider;
use Speedwork\Filesystem\Filesystem;

class PublishCommand extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Speedwork\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'vendor:publish {--force : Overwrite any existing files.}
            {--provider= : The service provider that has assets you want to publish.}
            {--tag=* : One or many tags that have assets you want to publish.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish any publishable assets from vendor packages';

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $tags = $this->option('tag');

        $tags = $tags ?: [null];

        foreach ((array) $tags as $tag) {
            $this->publishTag($tag);
        }
    }

    /**
     * Publishes the assets for a tag.
     *
     * @param string $tag
     *
     * @return mixed
     */
    private function publishTag($tag)
    {
        $paths = ServiceProvider::pathsToPublish(
            $this->option('provider'), $tag
        );

        if (empty($paths)) {
            return $this->comment("Nothing to publish for tag [{$tag}].");
        }

        foreach ($paths as $from => $to) {
            if (is_array($to)) {
                foreach ($to as $innerKey => $innerValue) {
                    $base       = $this->app['path.'.$from] ?: $this->app['path.base'];
                    $innerValue = $base.$innerValue;

                    $this->publish($innerKey, $innerValue);
                }
            } else {
                $to = $this->app['path.base'].$to;
                $this->publish($from, $to);
            }
        }

        $this->info("Publishing complete for tag [{$tag}]!");
    }

    protected function publish($from, $to)
    {
        if ($this->files->isFile($from)) {
            $this->publishFile($from, $to);
        } elseif ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);
        } elseif ($files = glob($from)) {
            foreach ($files as $file) {
                if ($this->files->isDirectory($file)) {
                    $this->publish($file, $to.basename($file));
                } else {
                    $this->publish($file, $to.basename($file));
                }
            }
        } else {
            $this->error("Can't locate path: <{$from}>");
        }
    }

    /**
     * Publish the file to the given path.
     *
     * @param string $from
     * @param string $to
     */
    protected function publishFile($from, $to)
    {
        if ($this->files->exists($to) && !$this->option('force')) {
            return;
        }

        $this->createParentDirectory(dirname($to));

        $this->files->copy($from, $to);

        $this->status($from, $to, 'File');
    }

    /**
     * Publish the directory to the given directory.
     *
     * @param string $from
     * @param string $to
     */
    protected function publishDirectory($from, $to)
    {
        $this->files->copyDirectory($from, $to);

        $this->status($from, $to, 'Directory');
    }

    /**
     * Create the directory to house the published files if needed.
     *
     * @param string $directory
     */
    protected function createParentDirectory($directory)
    {
        if (!$this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Write a status message to the console.
     *
     * @param string $from
     * @param string $to
     * @param string $type
     */
    protected function status($from, $to, $type)
    {
        $base = $this->app['path.base'];
        $from = str_replace($base, '', realpath($from));
        $to   = str_replace($base, '', realpath($to));

        $this->line('<info>Copied '.$type.'</info> <comment>['.$from.']</comment> <info>To</info> <comment>['.$to.']</comment>');
    }
}
