<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place orders during multishipping checkout flow.
 */
interface PlaceOrderInterface
{
    /**
     * Place orders.
     *
     * @param OrderInterface[] $orderList
     * @return array
     */
    public function place(array $orderList): array;
}
