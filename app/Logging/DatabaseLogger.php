<?php

namespace App\Logging;

use App\Logging\Handlers\DatabaseLogHandler;
use Monolog\Logger;

class DatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('database');
        $logger->pushHandler(new DatabaseLogHandler());
        return $logger;
    }
}
