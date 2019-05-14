<?php

namespace Fecon\OrsProducts\Api;

/**
 * 
 */
interface HandlerInterface
{

    const TYPE_STRING = 'string';
    const TYPE_SELECT = 'select';
    const TYPE_HTML = 'html';

    /**
     * Process raw data
     *
     * @param array $row
     * @param string $message
     * @return boolean
     */
    public function processData($row, &$message = '');
}