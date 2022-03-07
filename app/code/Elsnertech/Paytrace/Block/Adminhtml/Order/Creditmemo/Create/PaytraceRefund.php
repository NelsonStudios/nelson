<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Block\Adminhtml\Order\Creditmemo\Create;

class PaytraceRefund extends \Magento\Framework\View\Element\Template
{
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Elsnertech\Paytrace\Model\Api\Api $api,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_api = $api;
    }
    
    public function getCreditmemo()
    {
        return $this->_coreRegistry->registry('current_creditmemo');
    }
    
    /**
     * @return string
     */
    public function getOrderTransaction()
    {
        if ($this->getCreditmemo()->getInvoice()
            && ($this->getCreditmemo()
                ->getOrder()
                ->getPayment()
                ->getMethod()=='paytrace'
                || $this->getCreditmemo()
                ->getOrder()
                ->getPayment()
                ->getMethod()=='paytracevault')
        ) {
            return $this->getCreditmemo()
            ->getInvoice()
            ->getTransactionId();
        }
    }

    public function getOrderStatus()
    {
        $transactionId = $this->_api->getValidTransectionId(
            $this->getOrderTransaction()
        );
        if ($transactionId) {
            return $data = $this->_api->getStatusByTransecion(
                $transactionId
            );
        }
        return [];
    }
}
