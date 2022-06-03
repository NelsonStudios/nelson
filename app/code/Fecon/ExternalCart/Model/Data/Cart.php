<?php
/**
 * @copyright Copyright (c) Shop.Fecon.com, Inc. (http://www.shop.fecon.com)
 */

namespace Fecon\ExternalCart\Model\Data;

use Fecon\ExternalCart\Api\Data\CartInterface;
use Magento\Framework\DataObject;

class Cart extends DataObject implements CartInterface
{
    /**
     * @inheirtDoc
     */
    public function getCartItems()
    {
        return $this->getData(self::CART_ITEMS);
    }

    /**
     * @inheirtDoc
     */
    public function setCartItems(array $cartItems)
    {
        return $this->setData(self::CART_ITEMS, $cartItems);
    }
}
