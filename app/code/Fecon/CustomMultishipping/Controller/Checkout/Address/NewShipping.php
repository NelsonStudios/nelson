<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout\Address;

class NewShipping extends \Fecon\CustomMultishipping\Controller\Checkout\Address
{
    /**
     * Create New Shipping address Form
     *
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(
            \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State::STEP_SELECT_ADDRESSES
        );
        $this->_view->loadLayout();
        if ($addressForm = $this->_view->getLayout()->getBlock('customer_address_edit')) {
            $addressForm->setTitle(
                __('Create Shipping Address')
            )->setSuccessUrl(
                $this->_url->getUrl('*/*/shippingSaved')
            )->setErrorUrl(
                $this->_url->getUrl('*/*/*')
            );

            $this->_view->getPage()->getConfig()->getTitle()->set(
                $addressForm->getTitle() . ' - ' . $this->_view->getPage()->getConfig()->getTitle()->getDefault()
            );

            if ($this->_getCheckout()->getCustomerDefaultShippingAddress()) {
                $addressForm->setBackUrl($this->_url->getUrl('*/checkout/addresses'));
            } else {
                $addressForm->setBackUrl($this->_url->getUrl('*/cart/'));
            }
        }
        $this->_view->renderLayout();
    }
}
