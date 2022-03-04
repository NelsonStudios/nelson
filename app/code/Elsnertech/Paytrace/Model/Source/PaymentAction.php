<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model\Source;

use Magento\Payment\Model\Method\AbstractMethod;

class PaymentAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE,
                'label' => __('Authorize'),
            ],
            [
                'value' => AbstractMethod::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Sale'),
            ]
        ];
    }
}
