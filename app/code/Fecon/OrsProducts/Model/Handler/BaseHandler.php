<?php

namespace Fecon\OrsProducts\Model\Handler;

/**
 * 
 */
class BaseHandler implements \Fecon\OrsProducts\Api\HandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function processData($row, &$message = '')
    {
        $message = 'success';
        return true;
    }
}