<?php

namespace Fecon\ExternalCart\Controller\Cart;

use Fecon\ExternalCart\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteFactory;

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
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * $responseFactory
     *
     * @var ResponseFactory
     */
    protected $responseFactory;
    /**
     * $request
     *
     * @var Http
     */
    protected $request;
    /**
     * $checkoutSession
     *
     * @var Session
     */
    protected $checkoutSession;
    /**
     * $externalCartHelper
     *
     * @var Data
     */
    protected $cartHelper;
    /**
     * $$messageManager
     *
     * @var ManagerInterface
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
    /***
     * @var CartRepositoryInterface
     */
    private CartRepositoryInterface $cartRepository;
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ResponseFactory $responseFactory
     * @param QuoteFactory $quoteFactory
     * @param Http $request
     * @param Session $checkoutSession
     * @param Data $externalCartHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        ResponseFactory $responseFactory,
        QuoteFactory $quoteFactory,
        Http $request,
        Session $checkoutSession,
        Data $cartHelper,
        ManagerInterface $messageManager,
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
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
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
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
        $cartMaskId = $this->request->getParam('cartId');
        $logger->info("Customer Token: {$customerToken}");
        $logger->info("Cart Id: {$cartMaskId}");
        $customerId = null;
        $cartId = null;
        $redirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        if (empty($customerToken) && empty($cartMaskId)) {
            $logger->err("Missing Arguments");
            /* Go to home page */
            $redirect->setUrl('/');
        } else {
            $redirect->setUrl('/checkout/cart/index');
        }

        $logger->info("Access Token: {$this->access_token}");
        $customerData = $customerToken ? $this->cartHelper->makeCurlRequest(
            $this->origin,
            '/rest/V1/customers/me',
            $customerToken,
            'GET'
        ) : null;
        try {
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
                $this->cartHelper->makeUserLogin($customerInfo['email']);
                $logger->info("Login Success");
            }

            if (!empty($cartMaskId)) {
                $cartId = $this->maskedQuoteIdToQuoteId->execute($cartMaskId);
            }

            if (empty($cartId) && empty($customerId)) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t process your request right now. Please try again later.')
                );
                return $redirect;
            }
            //Just make sure cartMaskId from documoto is valid
            $cart = $customerId ? $this->cartRepository->getForCustomer($customerId) : $this->cartRepository->get(
                $cartId
            );
            $quoteId = $cart->getId();
            $logger->info("Quote Id: {$quoteId}");
            //Todo remove
            $this->checkoutSession->setQuoteId($quoteId);
            /* Redirect to cart page */
            $logger->info("Success Quote Id: {$quoteId}");
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $logger->crit("Error: {$e->getMessage()}");
            $this->messageManager->addErrorMessage(
                __('We can\'t process your request right now. Please try again later.')
            );
        }

        return $redirect;
    }
}
