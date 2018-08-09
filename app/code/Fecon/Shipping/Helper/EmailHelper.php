<?php

namespace Fecon\Shipping\Helper;

/**
 * Helper to send email
 *
 * 
 */
class EmailHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManager;
    
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface 
     */
    protected $inlineTranslation;
    
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder 
     */
    protected $transportBuilder;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;

        parent::__construct($context);
    }

    public function sendQuoteAvailableEmail($customer, $token)
    {
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
        
        $templateVars = array(
            'store' => $this->storeManager->getStore(),
            'customer' => $customer,
            'token' => $token
        );
        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from = [
            'name' => $name,
            'email' => $email
        ];
        $this->inlineTranslation->suspend();
        $to = array($customer->getEmail());
        $transport = $this->transportBuilder->setTemplateIdentifier('quote_available')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }
}