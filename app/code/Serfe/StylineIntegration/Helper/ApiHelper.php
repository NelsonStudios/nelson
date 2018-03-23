<?php

namespace Serfe\StylineIntegration\Helper;

/**
 * Description of ApiHelper
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class ApiHelper extends SoapClient
{
    /**
     * Get Part Info
     *
     * @param string $partNumber
     * @param string $qty
     * @param string $customerId
     * @return mixed
     */
    public function getPartInfo($partNumber, $qty, $customerId)
    {
        $partInfo = new \stdClass();
        $partInfo->ErpGetPartInfoRequest = new \stdClass();
        $partInfo->ErpGetPartInfoRequest->PartNumber = $partNumber;
        $partInfo->ErpGetPartInfoRequest->Quantity = $qty;
        $partInfo->ErpGetPartInfoRequest->CustomerId = $customerId;

        return $this->execRequest("GetPartInfo", $partInfo);
    }
    
    /**
     * Format cart data to pass it to the Web Service
     *
     * @param array $data
     * @return \stdClass
     */
    protected function parseCartData($data)
    {
        $GetCartErpSendShoppingCartRequestSiteAddress             = new \stdClass();
        $GetCartErpSendShoppingCartRequestSiteAddress->CustomerId = $data["address"]["CustomerId"];
        $GetCartErpSendShoppingCartRequestSiteAddress->Line1      = $data["address"]["Line1"];
        $GetCartErpSendShoppingCartRequestSiteAddress->Line2      = $data["address"]["Line2"];
        $GetCartErpSendShoppingCartRequestSiteAddress->Line3      = $data["address"]["Line3"];
        $GetCartErpSendShoppingCartRequestSiteAddress->City       = $data["address"]["City"];
        $GetCartErpSendShoppingCartRequestSiteAddress->State      = $data["address"]["State"];
        $GetCartErpSendShoppingCartRequestSiteAddress->Zipcode    = $data["address"]["Zipcode"];
        $GetCartErpSendShoppingCartRequestSiteAddress->Country    = $data["address"]["Country"];

        $GetCartErpSendShoppingCartRequestShipTo                  = new \stdClass();
        $GetCartErpSendShoppingCartRequestShipTo->SiteAddress     = $GetCartErpSendShoppingCartRequestSiteAddress;
        
        $ShoppingCartLine                                         = new \stdClass();
        $ShoppingCartLine->PartNumber                             = $data["cartLine"]["PartNumber"];
        $ShoppingCartLine->Quantity                               = $data["cartLine"]["Quantity"];
        $ShoppingCartLine->UOM                                    = $data["cartLine"]["UOM"];
        $ShoppingCartLine->Line                                   = $data["cartLine"]["Line"];
        
        
        $ArrayOfShoppingCartLine                                  = new \stdClass();
        $ArrayOfShoppingCartLine->ShoppingCartLine                = $ShoppingCartLine;

        $ErpSendShoppingCartRequest                               = new \stdClass();
        $ErpSendShoppingCartRequest->ShipTo                       = $GetCartErpSendShoppingCartRequestShipTo;
        $ErpSendShoppingCartRequest->ShoppingCartLines            = $ShoppingCartLine;
        $ErpSendShoppingCartRequest->Comments                     = $data["request"]["Comments"];
        $ErpSendShoppingCartRequest->EmailAddress                 = $data["request"]["EmailAddress"];
        $ErpSendShoppingCartRequest->AccountNumber                = $data["request"]["AccountNumber"];
        $ErpSendShoppingCartRequest->ShipVia                      = $data["request"]["ShipVia"];
        $ErpSendShoppingCartRequest->OrderCustomerName            = $data["request"]["OrderCustomerName"];
        $ErpSendShoppingCartRequest->CollectAccountNumber         = $data["request"]["CollectAccountNumber"];
        $ErpSendShoppingCartRequest->OrderStock                   = $data["request"]["OrderStock"];
        $ErpSendShoppingCartRequest->OrderPhoneNumber             = $data["request"]["OrderPhoneNumber"];
        $ErpSendShoppingCartRequest->DigabitERPTransactionType    = $data["request"]["DigabitERPTransactionType"];
        $ErpSendShoppingCartRequest->DigabitERPTransactionStatus  = $data["request"]["DigabitERPTransactionStatus"];

        $cartData                                                 = new \stdClass();
        $cartData->ErpSendShoppingCartRequest                     = $ErpSendShoppingCartRequest;
        
        return $cartData;
    }
    
    /**
     * Call the GetCart Web Service
     *
     * @param arrau $data
     * @return mixed
     */
    public function getCart($data)
    {
        $cardData = $this->parseCartData($data);

        return $this->execRequest("GetCart", $cardData);
    }
    
    /**
     * Returns the 
     *
     * @return mixed
     */
    public function getSoapTypes()
    {
        $wsdl = $this->getWsdl();
        $options = $this->getOptions();
        $client = new \Zend\Soap\Client($wsdl, $options);
        
        return $client->getTypes();
    }
}
