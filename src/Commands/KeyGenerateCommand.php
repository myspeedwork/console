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

class KeyGenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate {--show : Display the key instead of modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application key';

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            return $this->line('<comment>'.$key.'</comment>');
        }

        // Next, we will replace the application key in the environment file so it is
        // automatically setup for this developer. This key gets generated using a
        // secure random byte generator and is later base64 encoded for storage.
        $this->setInEnvFile('APP_KEY', $key);

        $this->app['config']['app.key'] = $key;

        $this->info("Application key [$key] set successfully.");
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
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            random_bytes(
                $this->app['config']['app.cipher'] == 'AES-128-CBC' ? 16 : 32
            )
        );
    }
}
