<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Block;

class SaveCard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $_httpContext;

    /**
     * @var \Elsnertech\Paytrace\Model\Paytracevault
     */
    protected $_paytraceVault;

    public function __construct(
        \Elsnertech\Paytrace\Model\Paytracevault $paytraceVault,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_paytraceVault = $paytraceVault;
        $this->_httpContext = $httpContext;
    }

    /**
     * @return string
     */
    public function isLoggedIn()
    {
        return $this->_httpContext->getValue(
            \Magento\Customer\Model\Context::CONTEXT_AUTH
        );
    }

    /**
     * @return boolean|string
     */
    public function getSaveCard()
    {
        if ($this->isLoggedIn()) {
            $saveCard = $this->_paytraceVault->getSavedCards();
            if ($saveCard) {
                return $saveCard;
            } else {
                return false;
            }
        }
    }
}
