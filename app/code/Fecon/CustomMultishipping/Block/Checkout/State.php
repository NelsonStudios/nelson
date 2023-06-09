<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Multishipping checkout state
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Fecon\CustomMultishipping\Block\Checkout;

class State extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State
     */
    protected $_multishippingState;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State $multishippingState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping\State $multishippingState,
        array $data = []
    ) {
        $this->_multishippingState = $multishippingState;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->_multishippingState->getSteps();
    }
}
