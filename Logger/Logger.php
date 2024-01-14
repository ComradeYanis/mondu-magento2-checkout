<?php
/**
 * Copyright (c) 2024.
 * wot2304@gmail.com
 * Yanis Yeltsyn
 */

declare(strict_types=1);


namespace Mondu\Mondu\Logger;

use Mondu\Mondu\Model\Config\MonduConfigProvider;

class Logger extends \Monolog\Logger
{
    /**
     * @var MonduConfigProvider
     */
    private $monduConfig;

    /**
     * @var string
     */
    protected $fallbackName = "MONDU";

    /**
     * @param MonduConfigProvider $monduConfig
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        MonduConfigProvider $monduConfig,
        $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->monduConfig = $monduConfig;
        parent::__construct($name ?? $this->fallbackName, $handlers, $processors);
    }

    /**
     *  Adds a log record at the INFO level.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        if ($this->monduConfig->getDebug()) {
            parent::info($message, $context);
        }
    }
}
