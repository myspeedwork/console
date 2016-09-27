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

use Speedwork\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;

class ServeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Serve the application on the PHP development server';

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function fire()
    {
        $base = $this->app['path.base'];
        chdir($base);

        $host   = $this->input->getOption('host');
        $port   = $this->input->getOption('port');
        $base   = ProcessUtils::escapeArgument($base);
        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        $this->info("Speedwork development server started on http://{$host}:{$port}/");

        passthru("{$binary} -S {$host}:{$port} {$base}/server.php");
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', 'localhost'],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', 8080],
        ];
    }
}
