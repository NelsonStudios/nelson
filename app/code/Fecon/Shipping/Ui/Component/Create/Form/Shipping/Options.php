<?php
namespace Fecon\Shipping\Ui\Component\Create\Form\Shipping;
 
use Magento\Framework\Data\OptionSourceInterface;
 
/**
 * Options tree for "Shipping Method" field
 */
class Options implements OptionSourceInterface
{
    const SHIPPING_METHODS = [
        'BEST' => 'Best Way to Ship',
        'FD2A' => 'Fed-Ex 2nd Day Air',
        'FDNA' => 'Fed-Ex Next Day Air',
        'FDNE' => 'Fed-Ex NDAEARLY',
        'FDXG' => 'Fed-Ex Ground',
        'FDXF' => 'Fed-Ex Freight',
        'FDIG' => 'Fed-Ex International Ground',
        'FDIP' => 'Fed-Ex International Priority',
        'FDC2' => 'Fed-Ex Collect - Two Day',
        'FDCE' => 'Fed-Ex Collect - Early AM',
        'FDCG' => 'Fed-Ex Collect - Ground',
        'FDCN' => 'Fed-Ex Collect - Next Day',
        'FDCS' => 'Fed-Ex Collect - Saturday',
        'UPSN' => 'UPS Next Day',
        'UPSB' => 'UPS Blue',
        'UPSG' => 'UPS Ground',
        'UP3D' => 'UPS 3 Day',
        'UPSS' => 'UPS Saturday',
        'UPAA' => 'UPS Next Day Air Early AM',
        'UPXI' => 'UPS Expedited International (2 Day)',
        'UPEI' => 'UPS Express International (Next Day)',
        'UPSE' => 'UPS Saver Express International (Next Day - Late)',
        'UPSI' => 'UPS Standard International (Ground)',
        'UPC2' => 'UPS Collect - Two Day',
        'UPCE' => 'UPS Collect - Early AM',
        'UPCG' => 'UPS Collect - Ground',
        'UPCN' => 'UPS Collect - Next Day',
        'UPCS' => 'UPS Collect - Saturday',
        'WILA' => 'Will Call Afternoon Pickup',
        'WILE' => 'Will Call Early Pickup',
        'FREC' => 'Freight Carrier Cheapest',
        'FREQ' => 'Freight Carrier Quickest',
        'OC' => 'Ocean Container',
        'N/A' => 'Free Shipping'
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