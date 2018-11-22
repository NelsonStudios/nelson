<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout;

use Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State;

class BackToShipping extends \Fecon\CustomMultishipping\Controller\Checkout
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(State::STEP_SHIPPING);
        $this->_getState()->unsCompleteStep(State::STEP_BILLING);
        $this->_redirect('*/*/shipping');
    }
}
