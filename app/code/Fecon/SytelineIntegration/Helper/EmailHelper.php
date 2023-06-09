<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Helper to send email
 */
class EmailHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    const ERROR_EMAIL_IDENTIFIER = 'syteline_error_notification';

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

    /**
     * @var ConfigHelper 
     */
    protected $configHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface 
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface 
     */
    protected $timezone;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        ConfigHelper $configHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->backendUrl = $backendUrl;
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
        $this->timezone = $timezone;

        parent::__construct($context);
    }

    /**
     * Send Syteline fail notification
     *
     * @param array $errors
     * @param int|null $orderId
     * @param int|null $productId
     * @return void
     */
    public function sendErrorEmailToAdmin($errors, $orderId, $productId)
    {
        $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId());
        $templateVars = $this->getTemplateVars($errors, $orderId, $productId);
        $from = $this->getFromSupportIdentity();
        $toEmail = $this->configHelper->getAdminEmail();
        if ($toEmail) {
            $to = array($toEmail);
            $this->inlineTranslation->suspend();
            $transport = $this->transportBuilder->setTemplateIdentifier($this::ERROR_EMAIL_IDENTIFIER)
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

    /**
     * Fill template vars
     *
     * @param array $errors
     * @param int|null $orderId
     * @param int|null $productId
     * @return array
     */
    protected function getTemplateVars($errors, $orderId, $productId)
    {
        $templateVars = [
            'product_id' => $productId,
            'order_id' => $orderId,
            'product_name' => '',
            'product_url' => '',
            'errors' => $errors,
            'date' => $this->timezone->formatDate(null, \IntlDateFormatter::SHORT, true)
        ];
        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);
                $templateVars['product_name'] = $product->getName();
                $templateVars['product_url'] = $product->getProductUrl();
            } catch (Exception $ex) {
            }
        }

        return $templateVars;
    }
}
