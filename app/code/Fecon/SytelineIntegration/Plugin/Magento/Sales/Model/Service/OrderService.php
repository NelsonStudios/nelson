<?php

namespace Fecon\SytelineIntegration\Plugin\Magento\Sales\Model\Service;

/**
 * Plugin for \Magento\Sales\Model\Service\OrderService class
 *
 * 
 */
class OrderService
{
    /**
     * Syteline Helper
     *
     * @var \Fecon\SytelineIntegration\Helper\SytelineHelper 
     */
    protected $sytelineHelper;

    /**
     * Constructor
     *
     * @param \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
     */
    public function __construct(
        \Fecon\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
    ) {
        $this->sytelineHelper = $sytelineHelper;
    }

    /**
     * Submit order data to Syteline after the order has been placed
     *
     * @param \Magento\Sales\Model\Service\OrderService $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $result
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterPlace(
        \Magento\Sales\Model\Service\OrderService $subject,
        $result
    ) {
        $this->sytelineHelper->submitCartToSyteline($result);

        return $result;
    }
}