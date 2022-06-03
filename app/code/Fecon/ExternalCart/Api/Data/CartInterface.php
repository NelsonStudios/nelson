<?php
/**
 * @copyright Copyright (c) Shop.Fecon.com, Inc. (http://www.shop.fecon.com)
 */

namespace Fecon\ExternalCart\Api\Data;

interface CartInterface
{
    const CART_ITEMS = "cart_items";

    /**
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     */
    public function getCartItems();

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $cartItems
     * @return mixed
     */
    public function setCartItems(array $cartItems);
}
