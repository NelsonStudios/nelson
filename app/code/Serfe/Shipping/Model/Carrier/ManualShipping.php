<?php

namespace Serfe\Shipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

/**
 * Shipping method to calculate rates manually
 *
 * @author Xuan Villagran <xuan@serfe.com>
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
     * Pre Order Repository
     *
     * @var \Serfe\Shipping\Api\PreorderRepositoryInterface 
     */
    protected $preOrderRepostory;

    /**
     * Checkout Session
     *
     * @var \Magento\Checkout\Model\Session 
     */
    protected $checkoutSession;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder 
     */
    protected $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Serfe\Shipping\Api\PreorderRepositoryInterface $preOrderRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Serfe\Shipping\Api\PreorderRepositoryInterface $preOrderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->preOrderRepository = $preOrderRepository;
        $this->checkoutSession = $checkoutSession;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        $shippingPrice = $this->getConfigData('price');
        $shippingPrice = 'Not available';

        $result = $this->rateResultFactory->create();

        if ($shippingPrice !== false) {
            foreach ($this->getAllowedMethods() as $code => $name) {
                $method = $this->rateMethodFactory->create();

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod($code);
                $method->setMethodTitle($name);
$shippingPrice = '0.00';
                if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                    $shippingPrice = '0.00';
                }

                $method->setPrice($shippingPrice);
                $method->setCost($shippingPrice);

                $result->append($method);
            }
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
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = [];
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }

        return $arr;
    }

    /**
     * Get Pre Order based on current quote
     *
     * @return \Serfe\Shipping\Api\Data\PreorderInterface|boolean
     */
    protected function getPreOrder()
    {
        $quoteId = $this->checkoutSession->getQuoteId();
        $preOrder = false;
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('quote_id', $quoteId)
                ->setPageSize(1)
                ->create();
            $preOrderItems = $this->preOrderRepostory->getList($searchCriteria);
            if ($preOrderItems->getTotalCount()) {
                foreach ($preOrderItems->getItems() as $preOrderItem) {
                    $preOrder = $preOrderItem;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $ex) {
            $this->_logger->critical($ex->getLogMessage());
        }

        return $preOrder;
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