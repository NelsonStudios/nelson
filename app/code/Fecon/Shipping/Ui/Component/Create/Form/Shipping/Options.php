<?php
namespace Fecon\Shipping\Ui\Component\Create\Form\Shipping;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options tree for "Shipping Method" field
 */
class Options implements OptionSourceInterface
{
    const SHIPPING_METHODS = [
        'BEST' => 'Best Way to Ship'
    ];

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (self::SHIPPING_METHODS as $shippingCode => $shippingMethod) {
            $option = [
                'value' => $shippingCode,
                'label' => $shippingMethod
            ];
            if ($shippingCode == 'UPSI') {
                $option['selected'] = true;
            }
            $options[] = $option;
        }

        return $options;
    }
}
