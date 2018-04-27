<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Transform Magento entities to arrays, in order to use with the Syteline Web Services
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class TransformData extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SYTELINE_CUSTOMER_ID = 'C000037';

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
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        parent::__construct($context);

        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->productRepository = $productRepository;
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
        if(!$shippingAddress) {
            throw new Exception('The order has null Shipping Address');
        }
        return [
            "address" => [
                "CustomerId" => $this::SYTELINE_CUSTOMER_ID,
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
                "AccountNumber" => "",
                "ShipVia" => "BEST",
                "OrderCustomerName" => $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname(),
                "CollectAccountNumber" => "",
                "OrderStock" => "Yes",
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
        return [
            "PartNumber" => $product->getPartNumber(),
            "Quantity" => $qty,
            "CustomerId" => $this::SYTELINE_CUSTOMER_ID
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
}