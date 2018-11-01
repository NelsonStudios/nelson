<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Controller\Checkout;

use Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State;

class BackToBilling extends \Fecon\CustomMultishipping\Controller\Checkout
{
    /**
     * Back to billing action
     *
     * @return void
     */
    public function execute()
    {
        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_getState()->unsCompleteStep(State::STEP_OVERVIEW);
        $this->_redirect('*/*/billing');
    }
}
