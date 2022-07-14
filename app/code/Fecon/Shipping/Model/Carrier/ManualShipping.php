<?php

namespace Fecon\Shipping\Model\Carrier;

use Fecon\Shipping\Ui\Component\Create\Form\Shipping\Options;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Shipping method to calculate rates manually
 *
 *
 */
class ManualShipping extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Code
     *
     * @var string
     */
    protected $_code = 'manualshipping';

    /**
     * Is Fixed
     *
     * @var boolean
     */
    protected $_isFixed = true;

    /**
     * Rate Result Factory
     *
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * Rate Method Factory
     *
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * Shipping Helper
     *
     * @var \Fecon\Shipping\Helper\ShippingHelper
     */
    protected $shippingHelper;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    protected $preorderHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Fecon\Shipping\Helper\ShippingHelper $shippingHelper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Fecon\Shipping\Helper\PreorderHelper $preorderHelper,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->shippingHelper = $shippingHelper;
        $this->stockRegistry = $stockRegistry;
        $this->preorderHelper = $preorderHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        $result = $this->rateResultFactory->create();
        $preorder = $this->preorderHelper->getPreorder();
        if ($preorder && $preorder->getId()){
            $methodCodes = $preorder->getData('shipping_method');
            $methodCodes = trim($methodCodes,'\'[');
            $methodCodes = trim($methodCodes,']\'');
            $methodCodes = explode(',',$methodCodes);
            foreach($methodCodes as $methodCode){
                $method = $this->rateMethodFactory->create();
                $methodCode = trim($methodCode,'"');
                if($methodCode == 'manualshipping_manualshipping'){
                    continue;
                }
                $title = $this->getConfigData('title');
                if (isset(Options::SHIPPING_METHODS[$methodCode])) {
                    $title = Options::SHIPPING_METHODS[$methodCode];
                }
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('name'));

                $method->setMethod($methodCode);
                $method->setMethodTitle($title);

                $shippingPrice = $preorder->getShippingPrice();

                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);
                $result->append($method);
            }
        }else{
            $method = $this->rateMethodFactory->create();
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            $shippingPrice = $this->shippingHelper->getShippingPrice($this->_code);

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $result->append($method);
        }

        return $result;
    }

    /**
     * getAllowedMethods
     *
     * @param array
     */
    public function getAllowedMethods()
    {
        $result = Options::SHIPPING_METHODS;
        $result[$this->_code] = $this->getConfigData('name');
        return $result;
    }

    public function isZipCodeRequired($countryId = null)
    {
        return true;
    }

    public function processAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        //Skip by item validation if there is no items in request
        if (!count($this->getAllItems($request))) {
            return $this;
        }

        $minWeight = (double)$this->getConfigData('min_package_weight');
        $errorMsg = '';
        $configErrorMsg = $this->getConfigData('specificerrmsg');
        $defaultErrorMsg = __('The shipping module is not available.');
        $showMethod = $this->getConfigData('showmethod');

        /** @var $item \Magento\Quote\Model\Quote\Item */
        foreach ($this->getAllItems($request) as $item) {
            $product = $item->getProduct();
            if ($product && $product->getId()) {
                $weight = $product->getWeight();
                $stockItemData = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $item->getStore()->getWebsiteId()
                );
                $doValidation = true;

                if ($stockItemData->getIsQtyDecimal() && $stockItemData->getIsDecimalDivided()) {
                    if ($stockItemData->getEnableQtyIncrements() && $stockItemData->getQtyIncrements()
                    ) {
                        $weight = $weight * $stockItemData->getQtyIncrements();
                    } else {
                        $doValidation = false;
                    }
                } elseif ($stockItemData->getIsQtyDecimal() && !$stockItemData->getIsDecimalDivided()) {
                    $weight = $weight * $item->getQty();
                }

                if ($doValidation && $weight < $minWeight) {
                    $errorMsg = $configErrorMsg ?? $defaultErrorMsg;
                    break;
                }
            }
        }

        if (!$errorMsg && !$request->getDestPostcode() && $this->isZipCodeRequired($request->getDestCountryId())) {
            $errorMsg = __('This shipping method is not available. Please specify the zip code.');
        }
        if ($errorMsg && $showMethod) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorMsg);

            return $error;
        } elseif ($errorMsg) {
            return false;
        }

        return $this;
    }

    public function getAllItems(RateRequest $request)
    {
        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    // Don't process children here - we will process (or already have processed) them below
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $items[] = $child;
                        }
                    }
                } else {
                    // Ship together - count compound item as one solid
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|false
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCode($type, $code = '')
    {
        $codes = [
            'method' => [
                'BEST' => __('Best Way to Ship'),
                'FREC' => __('Freight Carrier Cheapest'),
                'FREQ' => __('Freight Carrier Quickest'),
                'OC' => __('Ocean Container')
            ]
        ];

        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
}
