<?php

namespace Fecon\CustomMultishipping\Model\Checkout\Type;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Directory\Model\AllowedCountries;
use Psr\Log\LoggerInterface;

/**
 * Multishipping checkout model
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codingStandardsIgnoreFile
 * @since 100.0.2
 */
class Multishipping extends \Magento\Framework\DataObject
{
    /**
     * Quote shipping addresses items cache
     *
     * @var array
     */
    protected $_quoteShippingAddressesItems;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var \Magento\Payment\Model\Method\SpecificationInterface
     */
    protected $paymentSpecification;

    /**
     * Initialize dependencies.
     *
     * @var \Fecon\CustomMultishipping\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrder
     */
    protected $quoteAddressToOrder;

    /**
     * @var \Magento\Quote\Model\Quote\Item\ToOrderItem
     */
    protected $quoteItemToOrderItem;

    /**
     * @var \Magento\Quote\Model\Quote\Payment\ToOrderPayment
     */
    protected $quotePaymentToOrderPayment;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrderAddress
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var \Magento\Quote\Model\Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var AllowedCountries
     */
    private $allowedCountryReader;

    /**
     * @var \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     */
    private $shippingAssignmentProcessor;

    /**
     * @var Multishipping\PlaceOrderFactory
     */
    private $placeOrderFactory;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * Items Addresses for control
     *
     * @var array
     */
    protected $_itemsAddressesIds;

    /**
     * Virtual Addresses created
     *
     * @var array
     */
    protected $_virtualAddressesIds;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Quote\Model\Quote\AddressFactory $addressFactory
     * @param \Magento\Quote\Model\Quote\Address\ToOrder $quoteAddressToOrder
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
     * @param \Magento\Quote\Model\Quote\Payment\ToOrderPayment $quotePaymentToOrderPayment
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification
     * @param \Fecon\CustomMultishipping\Helper\Data $helper
     * @param OrderSender $orderSender
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     * @param array $data
     * @param \Magento\Quote\Api\Data\CartExtensionFactory|null $cartExtensionFactory
     * @param AllowedCountries|null $allowedCountryReader
     * @param Multishipping\PlaceOrderFactory $placeOrderFactory
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\Generic $session,
        \Magento\Quote\Model\Quote\AddressFactory $addressFactory,
        \Magento\Quote\Model\Quote\Address\ToOrder $quoteAddressToOrder,
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress,
        \Magento\Quote\Model\Quote\Payment\ToOrderPayment $quotePaymentToOrderPayment,
        \Magento\Quote\Model\Quote\Item\ToOrderItem $quoteItemToOrderItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Method\SpecificationInterface $paymentSpecification,
        \Fecon\CustomMultishipping\Helper\Data $helper,
        OrderSender $orderSender,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        array $data = [],
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory = null,
        AllowedCountries $allowedCountryReader = null,
        Multishipping\PlaceOrderFactory $placeOrderFactory = null,
        LoggerInterface $logger = null,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper = null
    ) {
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_session = $session;
        $this->_addressFactory = $addressFactory;
        $this->_storeManager = $storeManager;
        $this->paymentSpecification = $paymentSpecification;
        $this->helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->addressRepository = $addressRepository;
        $this->orderSender = $orderSender;
        $this->priceCurrency = $priceCurrency;
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->quoteAddressToOrder = $quoteAddressToOrder;
        $this->quoteItemToOrderItem = $quoteItemToOrderItem;
        $this->quotePaymentToOrderPayment = $quotePaymentToOrderPayment;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->totalsCollector = $totalsCollector;
        $this->cartExtensionFactory = $cartExtensionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Quote\Api\Data\CartExtensionFactory::class);
        $this->allowedCountryReader = $allowedCountryReader ?: ObjectManager::getInstance()
            ->get(AllowedCountries::class);
        $this->placeOrderFactory = $placeOrderFactory ?: ObjectManager::getInstance()
            ->get(Multishipping\PlaceOrderFactory::class);
        $this->_logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
        $this->dataObjectHelper = $dataObjectHelper ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\DataObjectHelper::class);

        $this->_virtualAddressesIds = array();
        $this->_itemsAddressesIds = array();
        parent::__construct($data);
        $this->_init();
    }

    /**
     * Initialize multishipping checkout.
     * Split virtual/not virtual items between default billing/shipping addresses
     *
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _init()
    {
        /**
         * reset quote shipping addresses and items
         */
        $quote = $this->getQuote();
        if (!$this->getCustomer()->getId()) {
            return $this;
        }

        if ($this->getCheckoutSession()->getCheckoutState() === \Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN
        ) {
            $this->getCheckoutSession()->setCheckoutState(true);
            /**
             * Remove all addresses
             */
            $addresses = $quote->getAllAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }

            $defaultShippingId = $this->getCustomerDefaultShippingAddress();
            if ($defaultShippingId) {
                $quote->getShippingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($defaultShippingId)
                );

                foreach ($this->getQuoteItems() as $item) {
                    /**
                     * Items with parent id we add in importQuoteItem method.
                     * Skip virtual items
                     */
                    if ($item->getParentItemId() || $item->getProduct()->getIsVirtual()) {
                        continue;
                    }
                    $quote->getShippingAddress()->addItem($item);
                }
            }

            $defaultBillingAddressId = $this->getCustomerDefaultBillingAddress();
            if ($defaultBillingAddressId) {
                $quote->getBillingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($defaultBillingAddressId)
                );
                foreach ($this->getQuoteItems() as $item) {
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if ($item->getProduct()->getIsVirtual()) {
                        $quote->getBillingAddress()->addItem($item);
                    }
                }
            }
            $this->save();
        }
        return $this;
    }

    /**
     * Get quote items assigned to different quote addresses populated per item qty.
     * Based on result array we can display each item separately
     *
     * @return array
     */
    public function getQuoteShippingAddressesItems()
    {
        if ($this->_quoteShippingAddressesItems !== null) {
            return $this->_quoteShippingAddressesItems;
        }
        $this->_quoteShippingAddressesItems = $this->getQuote()->getShippingAddressesItems();
        return $this->_quoteShippingAddressesItems;
    }

    /**
     * Remove item from address
     *
     * @param int $addressId
     * @param int $itemId
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    public function removeAddressItem($addressId, $itemId)
    {
        $address = $this->getQuote()->getAddressById($addressId);
        /* @var $address \Magento\Quote\Model\Quote\Address */
        if ($address) {
            $item = $address->getValidItemById($itemId);
            if ($item) {
                if ($item->getQty() > 1 && !$item->getProduct()->getIsVirtual()) {
                    $item->setQty($item->getQty() - 1);
                } else {
                    $address->removeItem($item->getId());
                }

                /**
                 * Require shipping rate recollect
                 */
                $address->setCollectShippingRates((bool)$this->getCollectRatesFlag());

                if (count($address->getAllItems()) == 0) {
                    $address->isDeleted(true);
                }

                $quoteItem = $this->getQuote()->getItemById($item->getQuoteItemId());
                if ($quoteItem) {
                    $newItemQty = $quoteItem->getQty() - 1;
                    if ($newItemQty > 0 && !$item->getProduct()->getIsVirtual()) {
                        $quoteItem->setQty($quoteItem->getQty() - 1);
                    } else {
                        $this->getQuote()->removeItem($quoteItem->getId());
                    }
                }
                $this->save();
            }
        }
        return $this;
    }

    /**
     * Assign quote items to addresses and specify items qty
     *
     * array structure:
     * array(
     *      $quoteItemId => array(
     *          'qty'       => $qty,
     *          'address'   => $customerAddressId
     *      )
     * )
     *
     * @param array $info
     * @param array $carrierInfo
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function setShippingItemsInformation($info, $carrierInfo = null)
    {
        if (is_array($info)) {
            $allQty = 0;
            $itemsInfo = [];
            foreach ($info as $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    $allQty += $data['qty'];
                    $itemsInfo[$quoteItemId] = $data;
                }
            }

            $maxQty = $this->helper->getMaximumQty();
            if ($allQty > $maxQty) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Maximum qty allowed for Shipping to multiple addresses is %1', $maxQty)
                );
            }
            $quote = $this->getQuote();
            $addresses = $quote->getAllShippingAddresses();
            foreach ($addresses as $address) {
                $quote->removeAddress($address->getId());
            }
            foreach ($info as $k => $itemData) {
                foreach ($itemData as $quoteItemId => $data) {
                    /* Custom code */
                    if(!empty($carrierInfo[$k]) && $carrierInfo[$k] == 1) { // Check for address that need to be splitted
                      $this->_virtualAddressesIds[] = $data['address']; // Set to "virtual" addresses created in order to delete them after.
                      $data['virtual'] = true; // Mark as virtual in order to validate after.
                    }else {
                      $data['virtual'] = false;
                    }
                    $this->_itemsAddressesIds[] = $data['address'];//add for control
                    /* End of custom code */
                    $this->_addShippingItem($quoteItemId, $data);
                }
            }
            /* Custom code */
            //@TODO remove this to the session
            $this->getCheckoutSession()->setVirtualAddressesIds($this->_virtualAddressesIds);
            /* End Custom code */
            $this->prepareShippingAssignment($quote);

            /**
             * Delete all not virtual quote items which are not added to shipping address
             * MultishippingQty should be defined for each quote item when it processed with _addShippingItem
             */
            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual() && !$_item->getParentItem() && !$_item->getMultishippingQty()
                ) {
                    $quote->removeItem($_item->getId());
                }
            }

            $billingAddress = $quote->getBillingAddress();
            if ($billingAddress) {
                $quote->removeAddress($billingAddress->getId());
            }

            $customerDefaultBillingId = $this->getCustomerDefaultBillingAddress();
            if ($customerDefaultBillingId) {
                $quote->getBillingAddress()->importCustomerAddressData(
                    $this->addressRepository->getById($customerDefaultBillingId)
                );
            }

            foreach ($quote->getAllItems() as $_item) {
                if (!$_item->getProduct()->getIsVirtual()) {
                    continue;
                }

                if (isset($itemsInfo[$_item->getId()]['qty'])) {
                    $qty = (int)$itemsInfo[$_item->getId()]['qty'];
                    if ($qty) {
                        $_item->setQty($qty);
                        $quote->getBillingAddress()->addItem($_item);
                    } else {
                        $_item->setQty(0);
                        $quote->removeItem($_item->getId());
                    }
                }
            }

            $this->save();
            $this->_eventManager->dispatch('checkout_type_multishipping_set_shipping_items', ['quote' => $quote]);
        }
        return $this;
    }

    /**
     * Add quote item to specific shipping address based on customer address id
     *
     * @param int $quoteItemId
     * @param array $data array('qty'=>$qty, 'address'=>$customerAddressId)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _addShippingItem($quoteItemId, $data)
    {
        $qty = isset($data['qty']) ? (int)$data['qty'] : 1;
        //$qty       = $qty > 0 ? $qty : 1;
        $addressId = isset($data['address']) ? $data['address'] : false;
        $isVirtualAddress = isset($data['virtual']) ? $data['virtual'] : false;
        $quoteItem = $this->getQuote()->getItemById($quoteItemId);

        if ($addressId && $quoteItem) {
            if (!$this->isAddressIdApplicable($addressId)) {
                throw new LocalizedException(__('Please check shipping address information.'));
            }

            /**
             * Skip item processing if qty 0
             */
            if ($qty === 0) {
                return $this;
            }
            $quoteItem->setMultishippingQty((int)$quoteItem->getMultishippingQty() + $qty);
            $quoteItem->setQty($quoteItem->getMultishippingQty());
            try {
                $address = $this->addressRepository->getById($addressId);
            } catch (\Exception $e) {
            }
            if (isset($address)) {
$debugflag = '';
                //first occurnce flagged to split was working buggy, we add this extra check to duplicate the address as customer may expect
                $addressOccurrence = array_count_values($this->_itemsAddressesIds);
                if (!($quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId()))) {
                  //can't find or retrieve address on existing Quote item
                  $quoteAddress = $this->_addressFactory
                  ->create()->importCustomerAddressData($address);
                  $this->getQuote()->addShippingAddress($quoteAddress);
$debugflag .='1';
                }elseif ($isVirtualAddress) {
                  //Here we force to add as a new address line
                  //remove from previous address line
                  $this->removeAddressItem($address->getId(),$quoteItemId);

                  //if the address is flagged as virtual, this means we are expecting to split order for same addresses
                  $quoteAddress = $this->_addressFactory
                  ->create()->importCustomerAddressData($address);
                  $this->getQuote()->addShippingAddress($quoteAddress);
$debugflag .='2';
                }elseif (!$isVirtualAddress
                  && in_array($address->getId(), $this->_virtualAddressesIds)
                  && !empty($addressOccurrence[$address->getId()])
                  && $addressOccurrence[$address->getId()]==2
                ) {
                  //This code is duplicated for a better read and understangin
                  // of this scenario. Here should enter if there was added
                  // previously one item flagged to split shipping, we must
                  //force to add as a new address line even if this wasn't
                  //flagged virtual address remove from previous address line
                  $this->removeAddressItem($address->getId(),$quoteItemId);

                  $quoteAddress = $this->_addressFactory->create()
                  ->importCustomerAddressData($address);
                  $this->getQuote()->addShippingAddress($quoteAddress);
$debugflag .='3';
                }else{
                  //this is to keep fallback to use Magento Multishipping workflow
                  $quoteAddress = $this->getQuote()->getShippingAddressByCustomerAddressId($address->getId());
$debugflag .='4';
                }

                //Associate customer address
                $quoteAddress->setCustomerAddressId($addressId);
                $quoteAddressItem = $quoteAddress->getItemByQuoteItemId($quoteItemId);
                //combine Qty for existing product in same address
                if ($quoteAddressItem && !$isVirtualAddress) {
                    $quoteAddressItem->setQty((int)($quoteAddressItem->getQty() + $qty));
$debugflag .='5';
                } else {
                  //add it as new item
                  $quoteAddress->addItem($quoteItem, $qty);
$debugflag .='6';
                }
$this->_logger->debug('Shipping ID-'.$address->getId().' debug(flag '.$debugflag.') Item ID '.$quoteItemId. ' ('. (is_object($quoteAddressItem)?'Joined '.$quoteAddressItem->getId():'first time') . ')' . ($isVirtualAddress?' is':' isnt'). ' virtual');
                /**
                 * Require shipping rate recollect
                 */
                $quoteAddress->setCollectShippingRates((bool)$this->getCollectRatesFlag());

            }
        }
        return $this;
    }

    /**
     * Reimport customer address info to quote shipping address
     *
     * @param int $addressId customer address id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    public function updateQuoteCustomerShippingAddress($addressId)
    {
        if (!$this->isAddressIdApplicable($addressId)) {
            throw new LocalizedException(__('Please check shipping address information.'));
        }
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (\Exception $e) {
            //
        }
        if (isset($address)) {
            $addresses = [];
            //due to our changes, same address ID can appears multiple times in the Quote
            foreach ($this->getQuote()->getAddressesCollection() as $a) {
                if ($a->getAddressType() == Address::TYPE_SHIPPING && !$a->isDeleted() && $a->getId()==$addressId) {
                    $a->setCollectShippingRates(true)->importCustomerAddressData($address);
                    $this->totalsCollector->collectAddressTotals($this->getQuote(), $a);
                    $this->quoteRepository->save($this->getQuote());
                }
            }
        }

        return $this;
    }

    /**
     * Reimport customer billing address to quote
     *
     * @param int $addressId customer address id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    public function setQuoteCustomerBillingAddress($addressId)
    {
        if (!$this->isAddressIdApplicable($addressId)) {
            throw new LocalizedException(__('Please check billing address information.'));
        }
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (\Exception $e) {
            //
        }
        if (isset($address)) {
            $quoteAddress = $this->getQuote()->getBillingAddress($addressId)->importCustomerAddressData($address);
            $this->totalsCollector->collectAddressTotals($this->getQuote(), $quoteAddress);
            $this->getQuote()->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }

        return $this;
    }

    /**
     * Assign shipping methods to addresses
     *
     * @param  array $methods
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setShippingMethods($methods)
    {
        $quote = $this->getQuote();
        $addresses = $quote->getAllShippingAddresses();
        /** @var  \Magento\Quote\Model\Quote\Address $address */
        foreach ($addresses as $address) {
            $addressId = $address->getId();
            if (isset($methods[$addressId])) {
                $address->setShippingMethod($methods[$addressId]);
            } elseif (!$address->getShippingMethod()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please select shipping methods for all addresses.')
                );
            }
        }
        $this->prepareShippingAssignment($quote);
        $this->save();
        return $this;
    }

    /**
     * Set payment method info to quote payment
     *
     * @param array $payment
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPaymentMethod($payment)
    {
        if (!isset($payment['method'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('A payment method is not defined.')
            );
        }
        if (!$this->paymentSpecification->isSatisfiedBy($payment['method'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The requested payment method is not available for multishipping.')
            );
        }
        $quote = $this->getQuote();
        $quote->getPayment()->importData($payment);
        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setTotalsCollectedFlag(false)->collectTotals();
        }
        $this->quoteRepository->save($quote);
        return $this;
    }

    /**
     * Prepare order based on quote address
     *
     * @param   \Magento\Quote\Model\Quote\Address $address
     * @return  \Magento\Sales\Model\Order
     * @throws  \Magento\Checkout\Exception
     */
    protected function _prepareOrder(\Magento\Quote\Model\Quote\Address $address)
    {
        $quote = $this->getQuote();
        $quote->unsReservedOrderId();
        $quote->reserveOrderId();
        $quote->collectTotals();

        $order = $this->_orderFactory->create();

        $this->dataObjectHelper->mergeDataObjects(
            \Magento\Sales\Api\Data\OrderInterface::class,
            $order,
            $this->quoteAddressToOrder->convert($address)
        );

        $order->setQuote($quote);
        $order->setBillingAddress($this->quoteAddressToOrderAddress->convert($quote->getBillingAddress()));

        if ($address->getAddressType() == 'billing') {
            $order->setIsVirtual(1);
        } else {
            $order->setShippingAddress($this->quoteAddressToOrderAddress->convert($address));
        }

        $order->setPayment($this->quotePaymentToOrderPayment->convert($quote->getPayment()));
        if ($this->priceCurrency->round($address->getGrandTotal()) == 0) {
            $order->getPayment()->setMethod('free');
        }

        foreach ($address->getAllItems() as $item) {
            $_quoteItem = $item->getQuoteItem();
            if (!$_quoteItem) {
                throw new \Magento\Checkout\Exception(
                    __('Item not found or already ordered')
                );
            }
            $item->setProductType(
                $_quoteItem->getProductType()
            )->setProductOptions(
                $_quoteItem->getProduct()->getTypeInstance()->getOrderOptions($_quoteItem->getProduct())
            );
            $orderItem = $this->quoteItemToOrderItem->convert($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        return $order;
    }

    /**
     * Validate quote data
     *
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validate()
    {
        $quote = $this->getQuote();

        /** @var $paymentMethod \Magento\Payment\Model\Method\AbstractMethod */
        $paymentMethod = $quote->getPayment()->getMethodInstance();
        if (!$paymentMethod->isAvailable($quote)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please specify a payment method.')
            );
        }

        $addresses = $quote->getAllShippingAddresses();
        foreach ($addresses as $address) {
            $addressValidation = $address->validate();
            if ($addressValidation !== true) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please check shipping addresses information.')
                );
            }
            $method = $address->getShippingMethod();
            $rate = $address->getShippingRateByCode($method);
            if (!$method || !$rate) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please specify shipping methods for all addresses.')
                );
            }

            // Checks if a country id present in the allowed countries list.
            if (
                !in_array(
                    $address->getCountryId(),
                    $this->allowedCountryReader->getAllowedCountries()
                )
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Some addresses cannot be used due to country-specific configurations.')
                );
            }
        }
        $addressValidation = $quote->getBillingAddress()->validate();
        if ($addressValidation !== true) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Please check billing address information.'));
        }
        return $this;
    }

    /**
     * Create orders per each quote address
     *
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     * @throws \Exception
     */
    public function createOrders()
    {
        $orderIds = [];
        $this->_validate();
        $shippingAddresses = $this->getQuote()->getAllShippingAddresses();
        $orders = [];

        if ($this->getQuote()->hasVirtualItems()) {
            $shippingAddresses[] = $this->getQuote()->getBillingAddress();
        }

        try {
            foreach ($shippingAddresses as $address) {
                $order = $this->_prepareOrder($address);

                $orders[] = $order;
                $this->_eventManager->dispatch(
                    'checkout_type_multishipping_create_orders_single',
                    ['order' => $order, 'address' => $address, 'quote' => $this->getQuote()]
                );
            }

            $paymentProviderCode = $this->getQuote()->getPayment()->getMethod();
            $placeOrderService = $this->placeOrderFactory->create($paymentProviderCode);
            $exceptionList = $placeOrderService->place($orders);
            $this->logExceptions($exceptionList);

            /** @var OrderInterface[] $failedOrders */
            $failedOrders = [];
            /** @var OrderInterface[] $successfulOrders */
            $successfulOrders = [];
            foreach ($orders as $order) {
                if (isset($exceptionList[$order->getIncrementId()])) {
                    $failedOrders[] = $order;
                } else {
                    $successfulOrders[] = $order;
                }
            }

            $placedAddressItems = [];
            foreach ($successfulOrders as $order) {
                $orderIds[$order->getId()] = $order->getIncrementId();
                if ($order->getCanSendNewEmailFlag()) {
                    $this->orderSender->send($order);
                }
                $placedAddressItems = array_merge($placedAddressItems, $this->getQuoteAddressItems($order));
            }

            $addressErrors = [];
            if (!empty($failedOrders)) {
                $this->removePlacedItemsFromQuote($shippingAddresses, $placedAddressItems);
                $addressErrors = $this->getQuoteAddressErrors(
                    $failedOrders,
                    $shippingAddresses,
                    $exceptionList
                );
            } else {
                $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId());
                $this->getQuote()->setIsActive(false);
                $this->quoteRepository->save($this->getQuote());
            }

            $this->_session->setOrderIds($orderIds);
            $this->_session->setAddressErrors($addressErrors);
            $this->_eventManager->dispatch(
                'checkout_submit_all_after',
                ['orders' => $orders, 'quote' => $this->getQuote()]
            );

            return $this;
        } catch (\Exception $e) {
            $this->_eventManager->dispatch('checkout_multishipping_refund_all', ['orders' => $orders]);
            throw $e;
        }
    }

    /**
     * Logs exceptions.
     *
     * @param \Exception[] $exceptionList
     * @return void
     */
    private function logExceptions(array $exceptionList)
    {
        foreach ($exceptionList as $exception) {
            $this->_logger->critical($exception);
        }
    }

    /**
     * Collect quote totals and save quote object
     *
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    public function save()
    {
        $this->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getQuote());
        return $this;
    }

    /**
     * Specify BEGIN state in checkout session whot allow reinit multishipping checkout
     *
     * @return \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping
     */
    public function reset()
    {
        $this->getCheckoutSession()->setCheckoutState(\Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    /**
     * Check if quote amount is allowed for multishipping checkout
     *
     * @return bool
     */
    public function validateMinimumAmount()
    {
        $minimumOrderActive = $this->_scopeConfig->isSetFlag(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($this->_scopeConfig->isSetFlag(
            'sales/minimum_order/multi_address',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
        ) {
            $result = !($minimumOrderActive && !$this->getQuote()->validateMinimumAmount());
        } else {
            $result = !($minimumOrderActive && !$this->validateMinimumAmountForAddressItems());
        }

        return $result;
    }

    /**
     * Get notification message for case when multishipping checkout is not allowed
     *
     * @return string
     */
    public function getMinimumAmountDescription()
    {
        $descr = $this->_scopeConfig->getValue(
            'sales/minimum_order/multi_address_description',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($descr)) {
            $descr = $this->_scopeConfig->getValue(
                'sales/minimum_order/description',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $descr;
    }

    /**
     * @return string
     */
    public function getMinimumAmountError()
    {
        $error = $this->_scopeConfig->getValue(
            'sales/minimum_order/multi_address_error_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (empty($error)) {
            $error = $this->_scopeConfig->getValue(
                'sales/minimum_order/error_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $error;
    }

    /**
     * Get order IDs created during checkout
     *
     * @param bool $asAssoc
     * @return array
     */
    public function getOrderIds($asAssoc = false)
    {
        $idsAssoc = $this->_session->getOrderIds();
        if ($idsAssoc !== null) {
            return $asAssoc ? $idsAssoc : array_keys($idsAssoc);
        }
        return [];
    }

    /**
     * Retrieve customer default billing address
     *
     * @return int|null
     */
    public function getCustomerDefaultBillingAddress()
    {
        $defaultAddressId = $this->getCustomer()->getDefaultBilling();
        return $this->getDefaultAddressByDataKey('customer_default_billing_address', $defaultAddressId);
    }

    /**
     * Retrieve customer default shipping address
     *
     * @return int|null
     */
    public function getCustomerDefaultShippingAddress()
    {
        $defaultAddressId = $this->getCustomer()->getDefaultShipping();
        return $this->getDefaultAddressByDataKey('customer_default_shipping_address', $defaultAddressId);
    }

    /**
     * Retrieve customer default address by data key
     *
     * @param string $key
     * @param string|null $defaultAddressIdFromCustomer
     * @return int|null
     */
    private function getDefaultAddressByDataKey($key, $defaultAddressIdFromCustomer)
    {
        $addressId = $this->getData($key);
        if (is_null($addressId)) {
            $addressId = $defaultAddressIdFromCustomer;
            if (!$addressId) {
                /** Default address is not available, try to find any customer address */
                $filter =  $this->filterBuilder->setField('parent_id')
                    ->setValue($this->getCustomer()->getId())
                    ->setConditionType('eq')
                    ->create();
                $addresses = (array)($this->addressRepository->getList(
                    $this->searchCriteriaBuilder->addFilters([$filter])->create()
                )->getItems());
                if ($addresses) {
                    $address = reset($addresses);
                    $addressId = $address->getId();
                }
            }
            $this->setData($key, $addressId);
        }

        return $addressId;
    }

    /**
     * Retrieve checkout session model
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckoutSession()
    {
        $checkout = $this->getData('checkout_session');
        if (is_null($checkout)) {
            $checkout = $this->_checkoutSession;
            $this->setData('checkout_session', $checkout);
        }
        return $checkout;
    }

    /**
     * Retrieve quote model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Retrieve quote items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getQuoteItems()
    {
        return $this->getQuote()->getAllItems();
    }

    /**
     * Retrieve customer session model
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Retrieve customer object
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        return $this->_customerSession->getCustomerDataObject();
    }

    /**
     * Check if specified address ID belongs to customer.
     *
     * @param $addressId
     * @return bool
     */
    protected function isAddressIdApplicable($addressId)
    {
        $applicableAddressIds = array_map(function($address) {
            /** @var \Magento\Customer\Api\Data\AddressInterface $address */
            return $address->getId();
        }, $this->getCustomer()->getAddresses());
        return !is_numeric($addressId) || in_array($addressId, $applicableAddressIds);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return \Magento\Quote\Model\Quote
     */
    private function prepareShippingAssignment($quote)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }
        /** @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = $this->getShippingAssignmentProcessor()->create($quote);
        $shipping = $shippingAssignment->getShipping();

        $shipping->setMethod(null);
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);
        return $quote->setExtensionAttributes($cartExtension);
    }

    /**
     * @return \Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor
     */
    private function getShippingAssignmentProcessor()
    {
        if (!$this->shippingAssignmentProcessor) {
            $this->shippingAssignmentProcessor = ObjectManager::getInstance()
                ->get(\Magento\Quote\Model\Quote\ShippingAssignment\ShippingAssignmentProcessor::class);
        }
        return $this->shippingAssignmentProcessor;
    }

    /**
     * Validate minimum amount for "Checkout with Multiple Addresses" when
     * "Validate Each Address Separately in Multi-address Checkout" is No.
     *
     * @return bool
     */
    private function validateMinimumAmountForAddressItems()
    {
        $result = true;
        $storeId = $this->getQuote()->getStoreId();

        $minAmount = $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $taxInclude = $this->_scopeConfig->getValue(
            'sales/minimum_order/tax_including',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $addresses = $this->getQuote()->getAllAddresses();

        $baseTotal = 0;
        foreach ($addresses as $address) {
            $taxes = $taxInclude ? $address->getBaseTaxAmount() : 0;
            $baseTotal += $address->getBaseSubtotalWithDiscount() + $taxes;
        }

        if ($baseTotal < $minAmount) {
            $result = false;
        }

        return $result;
    }

    /**
     * Remove successfully placed items from quote.
     *
     * @param \Magento\Quote\Model\Quote\Address[] $shippingAddresses
     * @param int[] $placedAddressItems
     * @return void
     */
    private function removePlacedItemsFromQuote(array $shippingAddresses, array $placedAddressItems)
    {
        foreach ($shippingAddresses as $address) {
            foreach ($address->getAllItems() as $addressItem) {
                if (in_array($addressItem->getId(), $placedAddressItems)) {
                    if ($addressItem->getProduct()->getIsVirtual()) {
                        $addressItem->isDeleted(true);
                    } else {
                        $address->isDeleted(true);
                    }

                    $this->decreaseQuoteItemQty($addressItem->getQuoteItemId(), $addressItem->getQty());
                }
            }
        }
        $this->save();
    }

    /**
     * Decrease quote item quantity.
     *
     * @param int $quoteItemId
     * @param int $qty
     * @return void
     */
    private function decreaseQuoteItemQty(int $quoteItemId, int $qty)
    {
        $quoteItem = $this->getQuote()->getItemById($quoteItemId);
        if ($quoteItem) {
            $newItemQty = $quoteItem->getQty() - $qty;
            if ($newItemQty > 0) {
                $quoteItem->setQty($newItemQty);
            } else {
                $this->getQuote()->removeItem($quoteItem->getId());
                $this->getQuote()->setIsMultiShipping(1);
            }
        }
    }

    /**
     * Returns quote address id that was assigned to order.
     *
     * @param OrderInterface $order
     * @param \Magento\Quote\Model\Quote\Address[] $addresses
     *
     * @return int
     * @throws NotFoundException
     */
    private function searchQuoteAddressId(OrderInterface $order, array $addresses): int
    {
        $items = $order->getItems();
        $item = array_pop($items);
        foreach ($addresses as $address) {
            foreach ($address->getAllItems() as $addressItem) {
                if ($addressItem->getId() == $item->getQuoteItemId()) {
                    return (int)$address->getId();
                }
            }
        }

        throw new NotFoundException(__('Quote address for failed order not found.'));
    }

    /**
     * @param OrderInterface[] $orders
     * @param \Magento\Quote\Model\Quote\Address[] $addresses
     * @param \Exception[] $exceptionList
     *
     * @return string[]
     * @throws NotFoundException
     */
    private function getQuoteAddressErrors(array $orders, array $addresses, array $exceptionList): array
    {
        $addressErrors = [];
        foreach ($orders as $failedOrder) {
            if (!isset($exceptionList[$failedOrder->getIncrementId()])) {
                throw new NotFoundException(__('Exception for failed order not found.'));
            }
            $addressId = $this->searchQuoteAddressId($failedOrder, $addresses);
            $addressErrors[$addressId] = $exceptionList[$failedOrder->getIncrementId()]->getMessage();
        }

        return $addressErrors;
    }

    /**
     * Returns quote address item id.
     *
     * @param OrderInterface $order
     * @return array
     */
    private function getQuoteAddressItems(OrderInterface $order): array
    {
        $placedAddressItems = [];
        foreach ($order->getItems() as $orderItem) {
            $placedAddressItems[] = $orderItem->getQuoteItemId();
        }

        return $placedAddressItems;
    }

    /**
     * Function added by (Fecon) in order to generate customer "virtual" address
     *
     * @param  [type] $observer [description]
     * @return [type]           [description]
     */
    private function cloneCustomerAddress($customerId, $address)
    {
        $addressFactory = ObjectManager::getInstance()->get(
            \Magento\Customer\Model\AddressFactory::class
        );
        $newAddress = $addressFactory->create();

        $newAddress->setCustomerId($customerId)
                ->setFirstname($address->getFirstname())
                ->setLastname($address->getLastname())
                ->setCountryId($address->getCountryId())
                ->setPostcode($address->getPostcode())
                ->setRegionId($address->getRegionId())
                ->setCity($address->getCity())
                ->setTelephone($address->getTelephone())
                ->setStreet($address->getStreet()[0])
                ->setSaveInAddressBook('1');
        try {
            $newAddress->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
        return $newAddress;
    }
}
