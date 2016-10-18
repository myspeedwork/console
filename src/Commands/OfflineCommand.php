<?php

namespace Speedwork\Console\Commands;

use Speedwork\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class OfflineCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change the application maintenance mode';

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $type = $this->input->getOption('type');

        if ($type == 'down') {
            $this->comment('Application is now in maintenance mode.');
            $this->setInEnvFile('APP_OFFLINE', true);
        } else {
            $this->info('Application is now live.');
            $this->setInEnvFile('APP_OFFLINE', false);
        }
    }

    /**
     * Set the application key in the environment file.
     *
     * @param string $key
     * @param string $value
     */
    protected function setInEnvFile($key, $value)
    {
        $env = file_get_contents($this->app['path.env']);

        $pattern = '/'.preg_quote($key).'([^\=]*)\=([^\n]*)/';

        if (preg_match($pattern, $env, $matches)) {
            file_put_contents($this->app['path.env'],
                str_replace($matches[0], $key.'='.$value, $env)
            );
        } else {
            file_put_contents($this->app['path.env'], $key.'='.$value."\n", FILE_APPEND);
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['type', null, InputOption::VALUE_OPTIONAL, 'Maintenance mode type', 'down'],
        ];
    }
}
