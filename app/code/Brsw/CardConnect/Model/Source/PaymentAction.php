<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Brsw\CardConnect\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 *
 * Authorize.net Payment Action Dropdown source
 */
class PaymentAction implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => \Brsw\CardConnect\Model\CardConnect::ACTION_AUTHORIZE,
                'label' => __('Authorize Only'),
            ],
            [
                'value' => \Brsw\CardConnect\Model\CardConnect::ACTION_AUTHORIZE_CAPTURE,
                'label' => __('Authorize and Capture')
            ]
        ];
    }
}
