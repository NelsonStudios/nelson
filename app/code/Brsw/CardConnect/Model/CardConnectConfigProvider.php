<?php
/**
 * @copyright Copyright (c) Shop.Fecon.com, Inc. (https://shop.fecon.com/)
 */

namespace Brsw\CardConnect\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class CardConnectConfigProvider implements ConfigProviderInterface
{
    /**
     * getConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        return [];
    }
}
