<?php

namespace Fecon\SytelineIntegration\Helper;

/**
 * Helper to manage Web Services data
 *
 * 
 */
class DataHandler
{
    /**
     * Validate the data for GetPartInfo Web Service
     *
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    public function isValidPartData($data, &$errors = [])
    {
        $requiredFields = $this->getPartRequiredFields();
        $validFields = $this->validField($data, $requiredFields, $errors);
        $isValidData = $validFields && is_numeric($data['Quantity']);
        
        return $isValidData;
    }

    /**
     * Validate the data for GetCart Web Service
     *
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    public function isValidGetCartData($data, &$errors = [])
    {
        $requiredFields = $this->getCartRequiredFields();
        $validFields = $this->validField($data, $requiredFields, $errors);

        return $validFields;
    }

    /**
     * Parse data to pass to the Web Service GetPart
     *
     * @param array $data
     * @return \stdClass
     */
    public function parsePartData($data)
    {
        $partInfo = new \stdClass();
        $partInfo->ErpGetPartInfoRequest = new \stdClass();
        $partInfo->ErpGetPartInfoRequest->PartNumber = $data['PartNumber'];
        $partInfo->ErpGetPartInfoRequest->Quantity = $data['Quantity'];
        $partInfo->ErpGetPartInfoRequest->CustomerId = $data['CustomerId'];

        return $partInfo;
    }

    /**
     * Format cart data to pass it to the Web Service
     *
     * @param array $data
     * @return \stdClass
     */
    public function parseCartData($data)
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


        $ArrayOfShoppingCartLine                                  = $this->getCartLines($data['cartLines']);

        $ErpSendShoppingCartRequest                               = new \stdClass();
        $ErpSendShoppingCartRequest->ShipTo                       = $GetCartErpSendShoppingCartRequestShipTo;
        $ErpSendShoppingCartRequest->ShoppingCartLines            = $ArrayOfShoppingCartLine;
        $ErpSendShoppingCartRequest->Comments                     = $data["request"]["Comments"];
        $ErpSendShoppingCartRequest->EmailAddress                 = $data["request"]["EmailAddress"];
        $ErpSendShoppingCartRequest->AccountNumber                = $data["request"]["AccountNumber"];
        $ErpSendShoppingCartRequest->SerialNumber                 = $data["request"]["SerialNumber"];
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
     * 
     * @param array $data
     * @return \stdClass[]
     */
    protected function getCartLines($data)
    {
        $cartLines = [];
        foreach ($data as $cartLine) {
            $ShoppingCartLine                                         = new \stdClass();
            $ShoppingCartLine->PartNumber                             = $cartLine["PartNumber"];
            $ShoppingCartLine->Quantity                               = $cartLine["Quantity"];
            $ShoppingCartLine->UOM                                    = $cartLine["UOM"];
            $ShoppingCartLine->Line                                   = $cartLine["Line"];
            $cartLines[] = $ShoppingCartLine;
        }

        return $cartLines;
    }

    /**
     * Get Required fields for GetPartInfo Web Service
     *
     * @return array
     */
    protected function getPartRequiredFields()
    {
        return [
            'PartNumber',
            'Quantity',
            'CustomerId'
        ];
    }

    /**
     * Get Required fields for GetCart Web Service
     *
     * @return array
     */
    protected function getCartRequiredFields()
    {
        return [
            "address" => [
                "CustomerId",
                "Line1",
                "City",
                "State",
                "Zipcode",
                "Country"
            ],
            "cartLines" => [[
                "PartNumber",
                "Quantity", 
                "UOM",
                "Line"
            ]],
            "request" => [
                "EmailAddress",
                "ShipVia",
                "OrderCustomerName",
                "OrderStock",
                "OrderPhoneNumber",
                "DigabitERPTransactionType",
                "DigabitERPTransactionStatus"
            ]
        ];
    }

    /**
     * Recursive validation of a field
     *
     * @param string|array $data
     * @param string|array $field
     * @param array $errors
     * @return boolean
     */
    protected function validField($data, $field, &$errors = [])
    {
        if (is_array($data)) {
            $isValid = $this->validArrayField($data, $field, $errors);
            
        } else {
            $isValid = $this->validStringField($data, $field, $errors);
        }
        
        return $isValid;
    }

    /**
     * Validate array Field
     *
     * @param array $data
     * @param array $field
     * @param array $errors
     * @return boolean
     */
    protected function validArrayField($data, $field, &$errors)
    {
        $isValid = true;
        if (is_array($field)) {
            $dataKeys = array_keys($data);
            foreach ($field as $key => $value) {
                $nextKey = is_array($value) ? $key : $value;
                if (!in_array($nextKey, $dataKeys)) {
                    $errors[] = '[' . $nextKey . '] is not set';
                    $isValid = false;
                } else {
                    $valueIsValid = $this->validField($data[$nextKey], $value, $errors);
                    $isValid = $isValid && $valueIsValid;
                }
            }
        } else {
            $errors[] = '[' . $field . '] is array when it should be an string';
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Validate string Field
     *
     * @param string $data
     * @param string $field
     * @param array $errors
     * @return boolean
     */
    protected function validStringField($data, $field, &$errors)
    {
        $isValid = true;
        if (empty($data) && $data !== '0') {
            $errors[] = '[' . $field . '] is empty';
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Validate if the $data is valid to make the Web Service call
     *
     * @param array $data
     * @param array $errors
     * @return boolean
     */
    public function isValidGetAddressesData($data, &$errors = [])
    {
        $requiredFields = $this->getAddressesRequiredFields();
        $isValidData = $this->validField($data, $requiredFields, $errors);
        
        return $isValidData;
    }

    /**
     * Get Required fields for GetAddresses Web Service
     *
     * @return array
     */
    protected function getAddressesRequiredFields()
    {
        return [
            'CustomerId'
        ];
    }

    /**
     * Parse data to pass to the Web Service GetAddresses
     *
     * @param array $data
     * @return \stdClass
     */
    public function parseGetAddressesData($data)
    {
        $partInfo = new \stdClass();
        $partInfo->ErpGetSiteAddressesRequest = new \stdClass();
        $partInfo->ErpGetSiteAddressesRequest->SiteInfo = new \stdClass();
        $partInfo->ErpGetSiteAddressesRequest->SiteInfo->CustomerId = $data['CustomerId'];

        return $partInfo;
    }
}
