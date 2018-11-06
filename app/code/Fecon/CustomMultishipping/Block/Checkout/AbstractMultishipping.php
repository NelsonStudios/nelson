<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Mustishipping checkout base abstract block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Fecon\CustomMultishipping\Block\Checkout;

class AbstractMultishipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    protected $_multishipping;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping $multishipping,
        array $data = []
    ) {
        $this->_multishipping = $multishipping;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve multishipping checkout model
     *
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    public function getCheckout()
    {
        return $this->_multishipping;
    }
}
