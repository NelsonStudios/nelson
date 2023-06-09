<?php

namespace Fecon\ExternalCart\Controller\Cart;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Controller to load quote and redirect to cart/checkout
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * $quoteCartRepositoryV1
     * @var string
     */
    protected $quoteCartRepositoryV1;
    /**
     * $quoteFactory
     *
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;
    /**
     * $responseFactory
     *
     * @var \Magento\Framework\App\ResponseFactory
     */
    protected $responseFactory;
    /**
     * $request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * $checkoutSession
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * $externalCartHelper
     *
     * @var \Fecon\ExternalCart\Helper\Data
     */
    protected $cartHelper;
    /**
     * $$messageManager
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * $protocol
     *
     * @var string
     */
    protected $protocol;
    /**
     * $hostname
     *
     * @var string
     */
    protected $hostname;
    /**
     * $port
     *
     * @var string
     */
    protected $port;
    /**
     * $port
     *
     * @var string
     */
    protected $access_token;
    /**
     * The "full domain" with protocol + domain + port
     * @var string
     */
    public $origin;
    /**
     * $customerLoggedIn
     * @var mixed integer/boolean
     */
    protected $customerLoggedIn = false;
    /**
     * $opts
     * Options array to be sent in SOAP request.
     * @var array
     */
    protected $opts;
    private CartRepositoryInterface $cartRepository;
    private QuoteIdMaskFactory $quoteIdMaskFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Fecon\ExternalCart\Helper\Data $externalCartHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Fecon\ExternalCart\Helper\Data $cartHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        CartRepositoryInterface $cartRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->responseFactory = $responseFactory;

        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->cartHelper = $cartHelper;
        $this->messageManager = $messageManager;

        $this->protocol = $this->cartHelper->protocol();
        $this->hostname = $this->cartHelper->hostname();
        $this->port = $this->cartHelper->port();
        $this->access_token = $this->cartHelper->access_token();

        if (!empty($this->protocol) && !empty($this->hostname)) {
            $this->origin = $this->protocol . $this->hostname;
        }
        if (!empty($this->port)) {
            $this->origin .= ':' . $this->port;
        }
        /* Add backend settings validation */
        if (empty($this->origin)) {
            throw new \Exception(
                __('Please check External Cart Settings in Admin section.')
            );
        }

        $this->cartRepository = $cartRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        parent::__construct($context);
    }

    /**
     * Execute pre checkout cart redirect action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/external_cart_' . date('Ymd') . '.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $customerToken = $this->request->getParam('customerToken');
        $cartId = $this->request->getParam('cartId');
        $logger->info("Customer Token: {$customerToken}");
        $customerId = null;
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        if (empty($customerToken) && empty($cartId)) {
            $logger->err("Missing Arguments");
            /* Go to home page */
            $redirect->setUrl('/');
        } else {
            $redirect->setUrl('/checkout/cart/index');
        }

        $logger->info("Access Token: {$this->access_token}");
        $customerData = $customerToken ? $this->cartHelper->makeCurlRequest($this->origin, '/rest/V1/customers/me',
            $customerToken, 'GET') : null;
        if (!empty($customerData)) {
            $customerInfo = $this->cartHelper->jsonDecode($customerData);
            if (empty($customerInfo['id'])) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t process your request right now. Please try again later.')
                );
                return $redirect;

            }
            $logger->info("Customer Data : {$customerData}");
            $customerId = $customerInfo['id'];
            /* Perform user login */
            $this->cartHelper->makeUserLogin($customerInfo['email'], true, $customerId);
            $logger->info("Login Success");
        } else {
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $session = $om->get('Magento\Customer\Model\Session');
            $session->setCustomerCodeNumber("");
            $session->setCustomerLoggedId("");
        }
        try {
            if ($customerId) {
                $cart = $this->cartRepository->getForCustomer($customerId);
            }
            if ($cartId) {
                $cart = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
                $cart = $this->cartRepository->get($cart->getQuoteId());
            }
            if ($cart) {
                $quoteId = $cart->getId();
                $logger->info("Quote Id: {$quoteId}");
                //Todo remove
                $this->checkoutSession->setQuoteId($quoteId);
                $this->checkoutSession->replaceQuote($cart);
                /* Redirect to cart page */
                $logger->info("Success Quote Id: {$quoteId}");
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $logger->crit("Error: {$e->getMessage()}");
            /* Display error and go to cart page */
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Please try again later.')
            );
        }

        return $redirect;
    }
}
