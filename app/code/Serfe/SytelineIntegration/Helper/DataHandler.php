<?php

namespace Serfe\SytelineIntegration\Helper;

/**
 * Helper to manage Web Services data
 *
 * @author Xuan Villagran <xuan@serfe.com>
 */
class DataHandler
{
    /**
     * Validate field
     *
     * @param array $data
     * @param string $field1
     * @param string $field2
     * @return boolean
     */
    protected function isValidField($data, $field1, $field2 = null, &$errors = [])
    {
//        if (isset($data[$field1])) {
//            $field = $data[$field1];
//        } else {
//            $errors[] = '[' . $field1 . '] is not set';
//        }
//        if (isset($data[$field1][$field2]) && $field2) {
//            $field = $data[$field1][$field2];
//        } else {
//            unset($field);
//            $errors[] = '[' . $field1 . '][' . $field2 . '] is not set';
//        }
//        if (empty($field) && $field !== '0') {
//            $errors[] = '[' . $field1 . '][' . $field2 . '] is empty';
//        }
//        $isValid = isset($field) && (!empty($field) || $field === '0');
//
//        return $isValid;
        
        $isValid = false;
        if ($field2) {
            if (!isset($data[$field1][$field2])) {
                $errors[] = '[' . $field1 . '][' . $field2 . '] is not set';
            } elseif (empty($data[$field1][$field2]) && $data[$field1][$field2] !== '0') {
                $errors[] = '[' . $field1 . '][' . $field2 . '] is empty';
            } else {
                $isValid = true;
            }
        } else {
            if (!isset($data[$field1])) {
                $errors[] = '[' . $field1 . '] is not set';
            } elseif (empty($data[$field1]) && $data[$field1] !== '0') {
                $errors[] = '[' . $field1 . '] is empty';
            } else {
                $isValid = true;
            }
        }
        
        return $isValid;
    }

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
        $validFields = true;
        foreach ($requiredFields as $requiredField) {
            if (!$this->isValidField($data, $requiredField, null, $errors)) {
                $validFields = false;
            }
        }
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
        $validFields = true;
        foreach ($requiredFields as $mainField => $requiredField) {
            foreach ($requiredField as $field) {
                if (!$this->isValidField($data, $mainField, $field, $errors)) {
                    $validFields = false;
                }
            }
        }

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
            "cartLine" => [
                "PartNumber",
                "Quantity", 
                "UOM",
                "Line"
            ],
            "request" => [
                "Comments",
                "EmailAddress",
                "AccountNumber",
                "ShipVia",
                "OrderCustomerName",
                "CollectAccountNumber",
                "OrderStock",
                "OrderPhoneNumber",
                "DigabitERPTransactionType",
                "DigabitERPTransactionStatus"
            ]
        ];
    }
}
