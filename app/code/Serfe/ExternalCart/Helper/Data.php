<?php
 
namespace Serfe\ExternalCart\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PROTOCOL = 'externalcart/active_display/protocol';
    const HOSTNAME = 'externalcart/active_display/hostname';
    const PORT = 'externalcart/active_display/port';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }
    /**
     * protocol
     * 
     * @return string protocol config value
     */
    public function protocol()
    {
        return $this->scopeConfig->getValue(
            self::PROTOCOL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * hostname
     * 
     * @return string hostname config value
     */
    public function hostname()
    {
        return $this->scopeConfig->getValue(
            self::HOSTNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * port 
     * 
     * @return string port config value
     */
    public function port()
    {
        return $this->scopeConfig->getValue(
            self::PORT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}