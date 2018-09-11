<?php 
namespace Fecon\ExternalCart\Controller\Cart;

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
    protected $externalCartHelper;
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

    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Action\Context       $context           
     * @param \Magento\Framework\App\ResponseFactory      $responseFactory   
     * @param \Magento\Quote\Model\QuoteFactory           $quoteFactory      
     * @param \Magento\Framework\App\Request\Http         $request           
     * @param \Magento\Checkout\Model\Session             $checkoutSession   
     * @param \Fecon\ExternalCart\Helper\Data             $externalCartHelper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager    
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Fecon\ExternalCart\Helper\Data $externalCartHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->responseFactory = $responseFactory;
        
        $this->quoteFactory = $quoteFactory;
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->cartHelper = $externalCartHelper;
        $this->messageManager = $messageManager;

        $this->protocol = $this->cartHelper->protocol();
        $this->hostname = $this->cartHelper->hostname();
        $this->port = $this->cartHelper->port();
        $this->access_token = $this->cartHelper->access_token();

        if(!empty($this->protocol) && !empty($this->hostname)) {
            $this->origin = $this->protocol . $this->hostname;
        }
        if(!empty($this->port)) {
            $this->origin .= ':' . $this->port;
        }
        /* Add backend settings validation */
        if(empty($this->origin)) {
            throw new \Exception(
                __('Please check External Cart Settings in Admin section.')
            );
        }

        parent::__construct($context);
    }

    /**
     * Execute pre checkout cart redirect action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $cartId = $this->request->getParam('cartId');
        $customerToken = $this->request->getParam('customerToken');
        if(!empty($cartId) || !empty($customerToken)) {
            /**
             * Get wsdl endpoint names based on guest or non-guest customers.
             */
            $this->quoteCartRepositoryV1 = (($customerToken)? 'quoteCartManagementV1' : 'quoteGuestCartRepositoryV1');
            $this->quoteGuestCartRepositoryV1 = (($customerToken)? 'quoteCartManagementV1GetCartForCustomer' : 'quoteGuestCartRepositoryV1Get');
            if($customerToken) {
                $this->opts['stream_context'] = stream_context_create([
                    'http' => [
                        'header' => sprintf('Authorization: Bearer %s', $this->access_token)
                    ]
                ]);
                $customerData = $this->cartHelper->makeCurlRequest($this->origin, '/rest/V1/customers/me', $customerToken, 'GET');
                if(!empty($customerData)) {
                    $customerInfo = $this->cartHelper->jsonDecode($customerData);
                    if(!empty($customerInfo['id'])) {
                        $requestData = ['customerId' => $customerInfo['id']];
                        /* Perform user login */
                        $this->cartHelper->makeUserLogin($customerInfo['email']);
                    }
                }
            } else {
                $requestData = ['cartId' => $cartId];
            }
            /* byPass Authorization access for internal use only */
            $client = new \SoapClient($this->origin . '/soap/?wsdl&services=' . $this->quoteCartRepositoryV1, (($this->opts)? $this->opts : [] ));
            try {
                /* Get quote */
                $cartInfo = $client->{$this->quoteGuestCartRepositoryV1}(((!empty($requestData))? $requestData : '' )); // If $requestData is empty an exception is thrown */
                if(!empty($cartInfo->result->id)) {
                    $quoteId = $cartInfo->result->id;
                    unset($cartInfo);
                    /* Load quote */
                    $q = $this->quoteFactory->create()->load($quoteId);
                    /* Load in checkout session as guest */
                    $this->checkoutSession->setQuoteId($quoteId);
                    /* Redirect to cart page */
                    $this->responseFactory->create()->setRedirect($this->origin . '/checkout/cart/index')->sendResponse();
                    return;
                } else {
                    /* Display error and go to cart page */
                    $this->displayErrorMsg('/checkout/cart/index');
                }
            } catch(\SoapFault $e) {
                /* Display error and go to cart page */
                $this->displayErrorMsg('/checkout/cart/index');
            }
        } else {
            /* Go to home page */
            $this->responseFactory->create()->setRedirect('/')->sendResponse();
            return;
        }
    }
    /**
     * displayErrorMsg
     * 
     * This function queue the error message and then redirect to specified path
     * in var $redirectPath otherwise redirects to "/"
     * 
     * @param  string $redirectPath The redirect path.
     * @return \Magento\Framework\Message\ManagerInterface
     */
    private function displayErrorMsg($redirectPath = '/') {
        $this->messageManager->addError(
            __('We can\'t process your request right now. Please try again later.')
        );
        $this->responseFactory->create()->setRedirect($redirectPath)->sendResponse(); 
        return;
    }
}