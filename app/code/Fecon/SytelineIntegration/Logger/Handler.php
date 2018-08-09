<?php

namespace Fecon\SytelineIntegration\Logger;

/**
 * Logger Error Handler
 *
 * 
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = \Monolog\Logger::ERROR;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/syteline.log';
}
