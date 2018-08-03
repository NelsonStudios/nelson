<?php 
namespace Fecon\ExternalCart\Controller\Cart;

/**
 * Controller to load quote and redirect to cart/checkout
 */
class Index extends \Magento\Framework\App\Action\Action
{

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
     * The "full domain" with protocol + domain + port
     * @var string
     */
    public $origin;

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
        $this->externalCartHelper = $externalCartHelper;
        $this->messageManager = $messageManager;

        $this->protocol = $this->externalCartHelper->protocol();
        $this->hostname = $this->externalCartHelper->hostname();
        $this->port = $this->externalCartHelper->port();

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
        if(!empty($cartId)) {
            /* byPass Authorization access for internal use only */
            $client = new \SoapClient($this->origin . '/soap/?wsdl&services=quoteGuestCartRepositoryV1');
            try {
                /* Get quote */
                $cartInfo = $client->quoteGuestCartRepositoryV1Get(array('cartId' => $cartId));
                if(!empty($cartInfo->result->id)) {
                    $quoteId = $cartInfo->result->id;
                    unset($cartInfo);
                    /* Load quote */
                    $q = $this->quoteFactory->create()->load($quoteId);
                    /* Load in checkout session as guest */
                    $this->checkoutSession->setQuoteId($quoteId);
                    /* Redirect to cart page */
                    $this->responseFactory->create()->setRedirect('/checkout/cart/index')->sendResponse();
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