<?php

namespace Fecon\Shipping\Helper;

/**
 * Helper to send email
 */
class EmailHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    const ADMIN_EMAIL_PATH = 'carriers/manualshipping/fulfillment_email_address';

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

    /**
     * @var \Magento\Backend\Model\UrlInterface 
     */
    protected $backendUrl;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Backend\Model\UrlInterface $backendUrl
    ) {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->backendUrl = $backendUrl;

        parent::__construct($context);
    }

    /**
     * Send quote available email
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param string $token
     * @param string $comments
     * @return void
     */
    public function sendQuoteAvailableEmail($customer, $token, $comments)
    {
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
        
        $templateVars = array(
            'store' => $this->storeManager->getStore(),
            'customer' => $customer,
            'token' => $token,
            'comments' => $comments
        );
        $from = $this->getFromSupportIdentity();
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

    /**
     * Send email notification to admin
     *
     * @param string $preorderId
     * @return void
     */
    public function sendAdminNotificationEmail($preorderId)
    {
        $url = $this->backendUrl->getUrl("admin/index/index");
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
        
        $templateVars = array(
            'store' => $this->storeManager->getStore(),
            'url' => $url,
            'preorderId' => $preorderId
        );
        $to = array($this->scopeConfig->getValue(self::ADMIN_EMAIL_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        if ($to) {
            $from = $this->getFromSupportIdentity();
            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder->setTemplateIdentifier('notify_admin')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Send notification of new preorder to customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function sendCustomerNotificationEmail($customer)
    {
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
        
        $templateVars = array(
            'store' => $this->storeManager->getStore(),
            'customer' => $customer
        );
        $from = $this->getFromSupportIdentity();
        $this->inlineTranslation->suspend();
        $to = array($customer->getEmail());
        $transport = $this->transportBuilder->setTemplateIdentifier('customer_notification')
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * Return configured support email and name
     *
     * @return array
     */
    protected function getFromSupportIdentity()
    {
        $email = $this->scopeConfig->getValue('trans_email/ident_support/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $name  = $this->scopeConfig->getValue('trans_email/ident_support/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $from = [
            'name' => $name,
            'email' => $email
        ];

        return $from;
    }
}