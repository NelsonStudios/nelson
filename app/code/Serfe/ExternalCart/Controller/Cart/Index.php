<?php 
namespace Serfe\ExternalCart\Controller\Cart;

/**
 * Controller to load and redirect to cart/checkout
 */
class Index extends \Magento\Framework\App\Action\Action
{

    /**
     * $resultPageFactory
     * 
     * @var \Magento\Framework\View\Result\PageFactory 
     */
    protected $resultPageFactory;

    /**
     * $quoteFactory
     * 
     * @var \Magento\Quote\Model\QuoteFactory 
     */
    protected $quoteFactory;
    /**
     * $quoteRepository
     * 
     * @var \Magento\Quote\Model\quoteRepository 
     */
    protected $quoteRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Serfe\Shipping\Helper\CustomerHelper $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // echo '<pre>';
        // print_r('LOAD QUOTE HERE');
        // echo '</pre>';
        // exit;
        /* Load quote id */
        $quoteId = '21';
        $q = $this->quoteFactory->create()->load($quoteId);
        echo '<pre>';
        print_r($q->getData());
        // print_r($q);
        echo '</pre>';
        exit;

        // $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        // $redirectPath = 'checkout';
        // $resultRedirect->setPath('checkout');

        // return $resultRedirect;
    }
}