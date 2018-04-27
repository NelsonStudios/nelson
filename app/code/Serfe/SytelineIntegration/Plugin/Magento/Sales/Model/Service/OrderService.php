<?php

namespace Serfe\SytelineIntegration\Plugin\Magento\Sales\Model\Service;

/**
 * Plugin for \Magento\Sales\Model\Service\OrderService class
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class OrderService
{
    /**
     * Syteline Helper
     *
     * @var \Serfe\SytelineIntegration\Helper\SytelineHelper 
     */
    protected $sytelineHelper;

    /**
     * Constructor
     *
     * @param \Serfe\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
     */
    public function __construct(
        \Serfe\SytelineIntegration\Helper\SytelineHelper $sytelineHelper
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