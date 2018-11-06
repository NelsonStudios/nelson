<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout\Address;

class SetBilling extends \Fecon\CustomMultishipping\Controller\Checkout\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        if ($addressId = $this->getRequest()->getParam('id')) {
            $this->_objectManager->create(
                \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping::class
            )->setQuoteCustomerBillingAddress(
                $addressId
            );
        }
        $this->_redirect('*/checkout/billing');
    }
}
