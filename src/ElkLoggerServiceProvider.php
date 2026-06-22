<?php

namespace Webmonet\ElkLogger;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;

class ElkLoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/elk-logger.php', 'elk-logger');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/elk-logger.php' => $this->app->configPath('elk-logger.php'),
        ], 'elk-logger-config');

        $this->registerLogChannel();
    }

    private function registerLogChannel(): void
    {
        /** @var Repository $config */
        $config = $this->app['config'];

        $channel = $config->get('elk-logger.channel_name', 'elasticsearch');

        if ($config->get("logging.channels.{$channel}") === null) {
            $config->set("logging.channels.{$channel}", [
                'driver' => 'custom',
                'via' => ElasticsearchLogger::class,
            ]);
        }
    }
}
