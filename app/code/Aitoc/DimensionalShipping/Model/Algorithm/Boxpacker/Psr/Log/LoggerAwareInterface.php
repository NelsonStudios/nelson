<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model\Algorithm\Boxpacker\Psr\Log;

/**
 * Describes a logger-aware instance.
 */
interface LoggerAwareInterface
{
    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger);
}
