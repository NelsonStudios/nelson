<?php

namespace Serfe\Shipping\Helper;

/**
 * Helper to send email
 *
 * @author Xuan Villagran <xuan@serfe.com>
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
        $from = array('email' => "fecon@mage2.com", 'name' => 'Fecon Store');
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