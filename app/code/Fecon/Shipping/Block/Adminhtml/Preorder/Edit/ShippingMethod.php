<?php

namespace Fecon\Shipping\Block\Adminhtml\Preorder\Edit;

use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Block to return Shipping Method information
 */
class ShippingMethod extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry 
     */
    protected $coreRegistry;

    /**
     * Carrier
     *
     * @var \Fecon\Shipping\Model\Carrier\ManualShipping 
     */
    protected $manualShipping;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping,
        \Magento\Framework\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->manualShipping = $manualShipping;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Get Shipping Title
     *
     * @return string
     */
    public function getShippingTitle()
    {
        $preorder = $this->coreRegistry->registry('fecon_shipping_preorder');
        $shippingMethod = $preorder->getData(PreorderInterface::SHIPPING_METHOD);
        $shippingCode = $this->getShippingCode($shippingMethod);
        $shippingTitle = $this->manualShipping->getCode('method', $shippingCode);

        return $this->escapeHtml($shippingTitle);
    }

    /**
     * Get shipping code
     *
     * @param string $shippingMethod
     * @return string
     */
    protected function getShippingCode($shippingMethod)
    {
        $underscorePos = strpos($shippingMethod, '_');
        $shippingCode = '';
        if ($underscorePos !== false) {
            $underscorePos++;
            $shippingCode = substr($shippingMethod, $underscorePos);
        }

        return $shippingCode;
    }
}