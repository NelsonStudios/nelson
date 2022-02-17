<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\RewardPoints\Model\Import;

/**
 * Class Logger
 * @package Aheadworks\RewardPoints\Model\Import
 */
class Logger
{
    /**
     * @var \Zend\Log\Logger
     */
    private $logger;

    /**
     * Initialize logger
     *
     * @param string $filename
     * @return $this
     */
    public function init($filename)
    {
        $writer = new \Zend_Log_Writer_Stream($filename);
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        return $this;
    }

    /**
     * Add message to log
     *
     * @param $message
     * @return $this
     */
    public function addMessage($message)
    {
        $this->logger->info($message);

        return $this;
    }
}
