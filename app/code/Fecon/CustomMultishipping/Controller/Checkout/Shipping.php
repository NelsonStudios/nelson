<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout;

use Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Framework\App\ResponseInterface;

class Shipping extends \Fecon\CustomMultishipping\Controller\Checkout
{
    /**
     * Multishipping checkout shipping information page
     *
     * @return  ResponseInterface|void
     */
    public function execute()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(State::STEP_SELECT_ADDRESSES)) {
            return $this->_redirect('*/*/addresses');
        }

        $this->_getState()->setActiveStep(State::STEP_SHIPPING);
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
