<?php

namespace Fecon\Shipping\Plugin;

/**
 * Plugin for ShippingDescription field of Orders
 */
class ShippingDescription
{

    protected $preorderHelper;

    public function __construct(\Fecon\Shipping\Helper\PreorderHelper $preorderHelper)
    {
        $this->preorderHelper = $preorderHelper;
    }

    public function afterGetShippingDescription(\Magento\Sales\Api\Data\OrderInterface $subject, $result)
    {
        if (strpos($subject->getShippingMethod(), 'manualshipping') !== false) {
            $preorder = $this->preorderHelper->getPreorderFromOrder($subject);
            if ($preorder) {
                $result = $this->preorderHelper->getShippingDescription($preorder);
            }
        }

        return $result;
    }
}