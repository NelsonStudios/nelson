<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Fecon\CustomMultishipping\Block\Checkout;

/**
 * Multishipping cart link
 *
 * @api
 * @since 100.0.2
 */
class Link extends \Magento\Framework\View\Element\Template
{
    /**
     * Multishipping helper
     *
     * @var \Fecon\CustomMultishipping\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Fecon\CustomMultishipping\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Fecon\CustomMultishipping\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('multishipping/checkout', ['_secure' => true]);
    }

    /**
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->helper->getQuote();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->helper->isMultishippingCheckoutAvailable()) {
            return '';
        }
        return parent::_toHtml();
    }
}
