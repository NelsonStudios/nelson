<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Plugin\Block\Adminhtml\Payment;

class View
{
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Registry $registry
    ) {
        $this->_backendUrl = $backendUrl;
        $this->_coreRegistry = $registry;
    }

    /**
     *
     * @param \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject
     * @param \Magento\Framework\View\Element\AbstractBlock $context
     * @param  \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
     * @return $this
     */
    public function beforePushButtons(
        \Magento\Backend\Block\Widget\Button\Toolbar\Interceptor $subject,
        \Magento\Framework\View\Element\AbstractBlock $context,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    ) {
        $this->_request = $context->getRequest();
        if ($this->_request->getFullActionName() == 'sales_order_view') {
            $transactionId = $this->getOrderTransaction();
            if ($transactionId) {
                $order = $this->_coreRegistry->registry('sales_order');
                $backendurl = $this->_backendUrl->getUrl(
                    "paytrace/transaction/statuscheck/",
                    ['transaction_id'=> $transactionId,
                    'order_id'=>$order->getId()]
                );
                $buttonList->add(
                    'paytrace_status',
                    [
                        'label' => __('Paytrace Status'),
                        'class' => 'paytrace-class',
                        'onclick' => 'setLocation(\'' . $backendurl . '\')'
                    ]
                );
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getOrderTransaction()
    {
        $order = $this->_coreRegistry->registry('sales_order');
        if ($order->getId() && $order->getPayment() &&
            ($order->getPayment()->getMethod()=='paytrace' ||
                $order->getPayment()->getMethod()=='paytracevault')
        ) {
            return $order->getPayment()->getLastTransId();
        }
    }
}
