<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout;

use Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Framework\App\ResponseInterface;

class Billing extends \Fecon\CustomMultishipping\Controller\Checkout
{
    /**
     * Validation of selecting of billing address
     *
     * @return boolean
     */
    protected function _validateBilling()
    {
        if (!$this->_getCheckout()->getQuote()->getBillingAddress()->getFirstname()) {
            $this->_redirect('*/checkout_address/selectBilling');
            return false;
        }
        return true;
    }

    /**
     * Multishipping checkout billing information page
     *
     * @return void|ResponseInterface
     */
    public function execute()
    {
        if (!$this->_validateBilling()) {
            return;
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(State::STEP_SHIPPING)) {
            return $this->_redirect('*/*/shipping');
        }

        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
