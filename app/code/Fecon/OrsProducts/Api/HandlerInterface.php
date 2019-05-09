<?php

namespace Fecon\OrsProducts\Api;

/**
 * 
 */
interface HandlerInterface
{

    /**
     * Process raw data
     *
     * @param array $row
     * @param string $message
     * @return boolean
     */
    public function processData($row, &$message = '');
}