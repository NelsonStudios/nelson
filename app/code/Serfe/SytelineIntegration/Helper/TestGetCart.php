<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Description of TestGetCart
 *
 * 
 */
class TestGetCart
{
    protected $sytelineHelper;
    
    protected $orderRepository;
    
    protected $regionFactory;
    
    protected $countryFactory;
    
    protected $productRepository;

    protected $submissionHelper;
    
    public function __construct(
        SytelineHelper $sytelineHelper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        SubmissionHelper $submissionHelper
    ) {
        $this->sytelineHelper = $sytelineHelper;
        $this->orderRepository = $orderRepository;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        $this->productRepository = $productRepository;
        $this->submissionHelper = $submissionHelper;
    }
    
    public function submitCart()
    {
        $order = $this->getOrder();
        $orderArray = $this->orderToArray($order);
        $response = $this->sytelineHelper->submitCartToSyteline($orderArray);
        $submissionData = [
            'success' => true,
            'testing' => true,
            'request' => print_r($orderArray, true),
            'response' => print_r($response, true),
            'errors' => (is_array($response) && isset($response['errors'])) ? $response['errors'] : null
        ];
        $this->submissionHelper->createSubmission($submissionData);
        var_dump($response);die();
    }
    
    protected function getOrder()
    {
        $order = $this->orderRepository->get('1');
        
        return $order;
    }
    
    protected function orderToArray($order)
    {
        return [
            "address" => [
                "CustomerId" => "C000037",
                "Line1" => $order->getShippingAddress()->getStreet()[0],
                "Line2" => "",
                "Line3" => "",
                "City" => $order->getShippingAddress()->getCity(),
                "State" => $this->getRegionCode($order->getShippingAddress()->getRegionId()),
                "Zipcode" => $order->getShippingAddress()->getPostCode(),
                "Country" => $this->getCountryName($order->getShippingAddress()->getCountryId())
            ],
            "cartLines" => $this->getLinesCart($order->getItems()),
            "request" => [
                "Comments" => (string) $order->getCustomerNote(),
                "EmailAddress" => $order->getCustomerEmail(),
                "AccountNumber" => "",
                "ShipVia" => "BEST",
                "OrderCustomerName" => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
                "CollectAccountNumber" => "",
                "OrderStock" => "Yes",
                "OrderPhoneNumber" => $order->getShippingAddress()->getTelephone(),
                "DigabitERPTransactionType" => "Order",
                "DigabitERPTransactionStatus" => "SUBMITTED"
            ]
        ];
    }
    
    protected function getRegionCode($regionId)
    {
        $region = $this->regionFactory->create()->load($regionId);
        
        return $region->getCode();
    }
    
    protected function getCountryName($countryCode)
    {
        $country = $this->countryFactory->create()->loadByCode($countryCode);

        return $country->getName();
    }
    
    protected function getLinesCart($orderItems)
    {
        $cartLines = [];
        $itemCount = 0;
        foreach ($orderItems as $item) {
            $product = $this->productRepository->getById($item->getProductId());
            $partNumber = $product->getPartNumber();
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
}
