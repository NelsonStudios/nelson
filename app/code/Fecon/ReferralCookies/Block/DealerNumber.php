<?php

namespace Fecon\ReferralCookies\Block;

use Magento\Framework\View\Element\Template;
use Fecon\ReferralCookies\Model\Config\DealerNumber as DealerNumberConfig;
use Magento\Store\Model\StoreManagerInterface;

class DealerNumber extends Template
{
    /**
     * @var DealerNumberConfig
     */
    protected $_dealerNumberConfig;

    /**
     * @param Template\Context $context
     * @param DealerNumberConfig $dealerNumberConfig
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context                $context,
        DealerNumberConfig              $dealerNumberConfig,
        StoreManagerInterface           $storeManager,
        array                           $data = []
    ) {
        $this->_dealerNumberConfig = $dealerNumberConfig;
        $this->_storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * @return DealerNumber
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * get Referral Cookies Enable.
     * @return mixed|string
     */
    public function getReferralCookiesEnable()
    {
        return $this->_dealerNumberConfig->getReferralCookiesEnabled();
    }

    /**
     * get Referral Cookies Time.
     * @return mixed|string
     */
    public function getReferralCookiesTime()
    {
        return $this->_dealerNumberConfig->getReferralCookiesTime();
    }

    /**
     * @return mixed|string
     */
    public function getDealerNumberInParamsUrl ()
    {
        return $this->_request->getParam('dealer_number') ?? '';
    }
}
