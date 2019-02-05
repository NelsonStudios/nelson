<?php

namespace Fecon\Shipping\Block\Adminhtml\Preorder\Edit;

use Fecon\Shipping\Api\Data\PreorderInterface;
use Fecon\Shipping\Ui\Component\Create\Form\Shipping\Options;

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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Fecon\Shipping\Model\Carrier\ManualShipping $manualShipping,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = array()
    ) {
        $this->manualShipping = $manualShipping;
        $this->coreRegistry = $coreRegistry;
        $this->serializer = $serializer;

        parent::__construct($context, $data);
    }

    /**
     * Get Shipping Title
     *
     * @return string
     */
    public function getShippingTitles()
    {
        $preorder = $this->coreRegistry->registry('fecon_shipping_preorder');
        $shippingMethod = $preorder->getData(PreorderInterface::SHIPPING_METHOD);
        $shippingTitle = $this->getShippingMethods($shippingMethod);

        return $this->escapeHtml($shippingTitle);
    }

    protected function getShippingMethods($shippingMethod)
    {
        $shippingArray = $this->serializer->unserialize($shippingMethod);
        $shippingTitles = [];
        foreach ($shippingArray as $shipping) {
            if (isset(Options::SHIPPING_METHODS[$shipping])) {
                $shippingTitle = Options::SHIPPING_METHODS[$shipping];
                $shippingTitles[] = $shippingTitle;
            }
        }

        return $shippingTitles;
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