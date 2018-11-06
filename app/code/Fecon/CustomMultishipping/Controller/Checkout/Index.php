<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout;

use \Fecon\CustomMultishipping\Controller\Checkout;

class Index extends \Fecon\CustomMultishipping\Controller\Checkout
{
    /**
     * Index action of Multishipping checkout
     *
     * @return void
     */
    public function execute()
    {
        $this->_getCheckoutSession()->setCartWasUpdated(false);
        $this->_redirect('*/*/addresses');
    }
}
