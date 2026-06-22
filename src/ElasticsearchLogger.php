<?php

namespace Webmonet\ElkLogger;

use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Handler\ElasticsearchHandler;
use Monolog\Level;
use Monolog\Logger;

class ElasticsearchLogger
{
    private static ?Logger $instance = null;

    public function __invoke(array $config = []): Logger
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $settings = array_replace_recursive(
            (array) config('elk-logger', []),
            $config,
        );

        $logger = new Logger($settings['channel_name'] ?? 'elasticsearch');

        $builder = ClientBuilder::create()->setHosts($settings['hosts'] ?? []);

        if (!empty($settings['username'])) {
            $builder->setBasicAuthentication(
                $settings['username'],
                $settings['password'] ?? '',
            );
        }

        $handler = new ElasticsearchHandler(
            $builder->build(),
            [
                'index' => $this->buildIndexName($settings),
                'type' => '_doc',
                'ignore_error' => (bool) ($settings['ignore_errors'] ?? true),
            ],
            $this->resolveLevel($settings['level'] ?? Level::Info),
            true,
        );

        $appName = $settings['app_name'] ?? 'app';
        $environment = $settings['environment'] ?? 'production';

        $logger->pushProcessor(function ($record) use ($appName, $environment) {
            $record['extra']['hostname'] = gethostname();
            $record['extra']['environment'] = $environment;
            $record['extra']['app_name'] = $appName . '_' . $environment;

            return $record;
        });

        $logger->setHandlers([$handler]);

        return self::$instance = $logger;
    }

    private function buildIndexName(array $settings): string
    {
        $prefix = $settings['index_prefix'] ?? 'log';
        $format = $settings['index_date_format'] ?? 'Y_m_d';

        return $prefix . '_' . date($format);
    }

    private function resolveLevel(Level|string|int $level): Level
    {
        if ($level instanceof Level) {
            return $level;
        }

        if (is_int($level)) {
            return Level::from($level);
        }

        return Level::fromName(ucfirst(strtolower($level)));
    }
}
