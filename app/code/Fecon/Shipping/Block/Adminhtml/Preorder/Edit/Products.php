<?php

namespace Fecon\Shipping\Block\Adminhtml\Preorder\Edit;

use Fecon\Shipping\Api\Data\PreorderInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Description of Products
 */
class Products extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface 
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Registry 
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory 
     */
    protected $currencyFactory;

    /**
     * @var \Magento\Directory\Model\Currency 
     */
    protected $currency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface 
     */
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->coreRegistry = $coreRegistry;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * Get Quote items
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[] | null
     */
    public function getItems()
    {
        $items = null;
        $preorder = $this->coreRegistry->registry('fecon_shipping_preorder');
        $status = (int) $preorder->getData(\Fecon\Shipping\Api\Data\PreorderInterface::STATUS);
        try{
            if ($status !== \Fecon\Shipping\Api\Data\PreorderInterface::STATUS_COMPLETED) {
                $quoteId = $preorder->getData(PreorderInterface::QUOTE_ID);
                $quote = $this->quoteRepository->get($quoteId);
                $items = $quote->getItems();
            }
        }catch(\Exception $e){
            echo "Error message => " .$e->getMessage();       
        }
        return $items;
    }

    /**
     * @param CartItemInterface $item
     * @return string
     */
    public function getProductName(CartItemInterface $item)
    {
        return $this->escapeHtml($item->getName());
    }

    /**
     * @param CartItemInterface $item
     * @return string
     */
    public function getSku(CartItemInterface $item)
    {
        return $this->escapeHtml($item->getSku());
    }

    /**
     * @param CartItemInterface $item
     * @return string
     */
    public function getPrice(CartItemInterface $item)
    {
        return $this->formatPricePrecision($item->getPrice(), 2);
    }

    /**
     * @param CartItemInterface $item
     * @return string
     */
    public function getQty(CartItemInterface $item)
    {
        return $this->escapeHtml($item->getQty());
    }

    /**
     * @param CartItemInterface $item
     * @return string
     */
    public function getSubtotal(CartItemInterface $item)
    {
        $subtotal = $item->getPrice() * $item->getQty();

        return $this->formatPricePrecision($subtotal, 2);
    }

    /**
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    protected function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getCurrency()->formatPrecision($price, $precision, [], true, $addBrackets);
    }


    /**
     * Get currency model instance
     *
     * @return \Magento\Directory\Model\Currency
     */
    protected function getCurrency()
    {
        if ($this->currency === null) {
            $this->currency = $this->currencyFactory->create();
            $this->currency->load($this->getCurrencyCode());
        }

        return $this->currency;
    }

    /**
     * @return string
     */
    protected function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
}