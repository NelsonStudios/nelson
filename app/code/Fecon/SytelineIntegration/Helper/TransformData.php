<?php

namespace Fecon\SytelineIntegration\Helper;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Transform Magento entities to arrays, in order to use with the Syteline Web Services
 *
 * 
 */
class TransformData extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     * @var \Magento\Directory\Model\RegionFactory 
     */
    protected $regionFactory;

    /**
     *
     * @var \Magento\Directory\Model\CountryFactory 
     */
    protected $countryFactory;

    /**
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface 
     */
    protected $productRepository;

    /**
     * @var \Fecon\SytelineIntegration\Helper\ConfigHelper 
     */
    protected $configHelper;

    /**
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
    * @var SerializerInterface
    */
   private $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Fecon\SytelineIntegration\Helper\ConfigHelper $configHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);

        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->productRepository = $productRepository;
        $this->configHelper = $configHelper;
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->serializer = $serializer;
    }
    /**
     * Transform Magento order to array
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     * @throws Exception
     */
    public function orderToArray($order)
    {
        $shippingAddress = $order->getShippingAddress();
        $customer = $this->getCustomer($order);
        if(!$shippingAddress) {
            throw new Exception('The order has null Shipping Address');
        }
        return [
            "address" => [
                "CustomerId" => $this->getSytelineCustomerId($customer),
                "Line1" => $this->getShippingAddress($shippingAddress),
                "Line2" => "",
                "Line3" => "",
                "City" => (string) $shippingAddress->getCity(),
                "State" => $this->getRegionCode($shippingAddress->getRegionId()),
                "Zipcode" => (string) $shippingAddress->getPostCode(),
                "Country" => $this->getCountryName($shippingAddress->getCountryId())
            ],
            "cartLines" => $this->getLinesCart($order->getItems()),
            "request" => [
                "Comments" => (string) $order->getCustomerNote(),
                "EmailAddress" => (string) $order->getCustomerEmail(),
                "AccountNumber" => $this->getAccountNumber($order),
                "SerialNumber" => $this->getSerialNumber($order),
                "ShipVia" => "BEST",
                "OrderCustomerName" => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
                "CollectAccountNumber" => "",
                "OrderStock" => $this->getOrderStock($order),
                "OrderPhoneNumber" => (string) $shippingAddress->getTelephone(),
                "DigabitERPTransactionType" => "Order",
                "DigabitERPTransactionStatus" => "SUBMITTED"
            ]
        ];
    }
    
    /**
     * Generate array data to send via GetPartInfo Web Service
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $qty
     * @return array
     */
    public function productToArray(\Magento\Catalog\Model\Product $product, $qty)
    {
        $customer = $this->getCustomer();

        return [
            "PartNumber" => $product->getPartNumber(),
            "Quantity" => $qty,
            "CustomerId" => $this->getSytelineCustomerId($customer)
        ];
    }

    /**
     * Get region code based on region id
     * 
     * @param string $regionId
     * @return string
     */
    protected function getRegionCode($regionId)
    {
        $region = $this->regionFactory->create()->load($regionId);

        return (string) $region->getCode();
    }

    /**
     * Get country name based on country code
     *
     * @param string $countryCode
     * @return string
     */
    protected function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);

        return (string) $country->getName();
    }

    /**
     * Get Cart Lines
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $orderItems
     * @return array
     */
    protected function getLinesCart($orderItems)
    {
        $cartLines = [];
        $itemCount = 0;
        foreach ($orderItems as $item) {
            $partNumber = $this->getPartNumber($item->getProductId());
            $cartLine = [
                "PartNumber" => $partNumber,
                "Quantity" => $item->getQtyOrdered(),
                "UOM" => "EA",
                "Line" => (string) $itemCount
            ];
            $cartLines[] = $cartLine;
            $itemCount++;
        }

        return $cartLines;
    }
    
    /**
     * Get Shipping Address
     *
     * @param \Magento\Sales\Model\Order\Address $shippingAddress
     * @return type
     */
    protected function getShippingAddress($shippingAddress)
    {
        $address = isset($shippingAddress->getStreet()[0]) ? $shippingAddress->getStreet()[0] : '';
        
        return (string) $address;
    }
    
    /**
     * Get Part Number from $productId
     *
     * @param string $productId
     * @return string
     */
    protected function getPartNumber($productId)
    {
        try {
            $loadedProduct = $this->productRepository->getById($productId);
            $partNumber = (string) $loadedProduct->getPartNumber();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) {
            $partNumber = '';
        }

        return $partNumber;
    }

    /**
     * Get customer to be used in the Syteline request
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomer($order = null)
    {
        $customer = null;
        $customerId = null;
        if (!$order && $this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
        } elseif ($order) {
            $customerId = $order->getCustomerId();
        }

        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
            } catch (\Exception $ex) {
            }
        }

        return $customer;
    }

    /**
     * Get Syteline ID to be used in the Syteline request
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|null $customer
     * @return string
     */
    protected function getSytelineCustomerId($customer)
    {
        $sytelineCustomerId = $this->configHelper->getDefaultSytelineCustomerId();
        //if customer is logged in and attribute 'customer_number' is not empty, 
        //then, we should use the customer ID from syteline not default from config
        if ($customer && $customer->getCustomAttribute('customer_number') ) {
            $customerId = $customer->getCustomAttribute('customer_number')->getValue();
            $sytelineCustomerId = $customerId ? $customerId : $sytelineCustomerId;
        }

        return $sytelineCustomerId;
    }

    /**
     * Generate array data to send via GetPartInfo Web Service
     *
     * @return array
     */
    public function customerToArray()
    {
        $defaultCustomerId = $this->configHelper->getDefaultSytelineCustomerId();
        $customer = $this->getCustomer();
        $customerId = $this->getSytelineCustomerId($customer);
        if ($customerId == $defaultCustomerId) {
            $customerId = null;
        }

        return [
            "CustomerId" => $customerId
        ];
    }

    protected function getSytelineExtraField($order, $key)
    {
        $value = '';
        try {
            $data = $this->serializer->unserialize($order->getSytelineCheckoutExtraFields());
        } catch (\Exception $ex) { }
        if (isset($data[$key])) {
            $value = $data[$key];
        }

        return $value;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getAccountNumber($order)
    {
        return $this->getSytelineExtraField($order, 'purchaseOrderNumber');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getSerialNumber($order)
    {
        return $this->getSytelineExtraField($order, 'serialNumber');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    protected function getOrderStock($order)
    {
        $orderStock = 'Yes';
        $orderData = $this->getSytelineExtraField($order, 'orderStock');
        if ($orderData) {
            $orderStock = $orderData;
        }

        return $orderStock;
    }
}