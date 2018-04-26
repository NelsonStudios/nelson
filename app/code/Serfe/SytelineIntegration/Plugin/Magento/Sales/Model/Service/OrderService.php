<?php

namespace Serfe\SytelineIntegration\Plugin\Magento\Sales\Model\Service;

/**
 * Description of OrderService
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

    public function afterPlace(
        \Magento\Sales\Model\Service\OrderService $subject,
        $result
    ) {
        $this->sytelineHelper->submitCartToSyteline($result);

        return $result;
    }
}