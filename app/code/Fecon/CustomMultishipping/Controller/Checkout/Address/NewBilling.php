<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout\Address;

class NewBilling extends \Fecon\CustomMultishipping\Controller\Checkout\Address
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(
                __('Create Billing Address')
            )->setSuccessUrl(
                $this->_url->getUrl('*/*/selectBilling')
            )->setErrorUrl(
                $this->_url->getUrl('*/*/*')
            )->setBackUrl(
                $this->_url->getUrl('*/*/selectBilling')
            );

            $this->_view->getPage()->getConfig()->getTitle()->set(
                $addressForm->getTitle() . ' - ' . $this->_view->getPage()->getConfig()->getTitle()->getDefault()
            );
        }
        $this->_view->renderLayout();
    }
}
