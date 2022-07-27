<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model\Rewrite\Ups;

use Aitoc\DimensionalShipping\Plugin\Shipping;
use Magento\Ups\Model\Carrier as UpsCarrier;

class Carrier extends UpsCarrier
{
    protected function _getXmlQuotes()
    {
//AITOC CUSTOMIZATION
        $dimensions = ['height' => 0, 'width' => 0, 'length' => 0];
        $dimensionData = $this->_request->getData(Shipping::DIMENSIONS);
        if (!empty($dimensionData)) {
            $dimensions = array_shift($dimensionData);
            $this->_request->setData(Shipping::DIMENSIONS, $dimensionData);
        }
//AITOC CUSTOMIZATION END
        $url = $this->getConfigData('gateway_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest;
        $debugData['accessRequest'] = $this->filterDebugData($xmlRequest);

        $rowRequest = $this->_rawRequest;
        if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
            $destPostal = substr((string)$rowRequest->getDestPostal(), 0, 5);
        } else {
            $destPostal = $rowRequest->getDestPostal();
        }
        $params = [
            'accept_UPS_license_agreement' => 'yes',
            '10_action' => $rowRequest->getAction(),
            '13_product' => $rowRequest->getProduct(),
            '14_origCountry' => $rowRequest->getOrigCountry(),
            '15_origPostal' => $rowRequest->getOrigPostal(),
            'origCity' => $rowRequest->getOrigCity(),
            'origRegionCode' => $rowRequest->getOrigRegionCode(),
            '19_destPostal' => $destPostal,
            '22_destCountry' => $rowRequest->getDestCountry(),
            'destRegionCode' => $rowRequest->getDestRegionCode(),
            '23_weight' => $rowRequest->getWeight(),
            '47_rate_chart' => $rowRequest->getPickup(),
            '48_container' => $rowRequest->getContainer(),
            '49_residential' => $rowRequest->getDestType(),
        ];

        if ($params['10_action'] == '4') {
            $params['10_action'] = 'Shop';
            $serviceCode = null;
        } else {
            $params['10_action'] = 'Rate';
            $serviceCode = $rowRequest->getProduct() ? $rowRequest->getProduct() : null;
        }
        $serviceDescription = $serviceCode ? $this->getShipmentByCode($serviceCode) : '';

        $xmlParams = <<<XMLRequest
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>{$params['10_action']}</RequestOption>
  </Request>
  <PickupType>
          <Code>{$params['47_rate_chart']['code']}</Code>
          <Description>{$params['47_rate_chart']['label']}</Description>
  </PickupType>

  <Shipment>
XMLRequest;

        if ($serviceCode !== null) {
            $xmlParams .= "<Service>" .
                "<Code>{$serviceCode}</Code>" .
                "<Description>{$serviceDescription}</Description>" .
                "</Service>";
        }

        $xmlParams .= <<<XMLRequest
      <Shipper>
XMLRequest;

        if ($this->getConfigFlag('negotiated_active') && ($shipperNumber = $this->getConfigData('shipper_number'))) {
            $xmlParams .= "<ShipperNumber>{$shipperNumber}</ShipperNumber>";
        }

        if ($rowRequest->getIsReturn()) {
            $shipperCity = '';
            $shipperPostalCode = $params['19_destPostal'];
            $shipperCountryCode = $params['22_destCountry'];
            $shipperStateProvince = $params['destRegionCode'];
        } else {
            $shipperCity = $params['origCity'];
            $shipperPostalCode = $params['15_origPostal'];
            $shipperCountryCode = $params['14_origCountry'];
            $shipperStateProvince = $params['origRegionCode'];
        }

        $xmlParams .= <<<XMLRequest
      <Address>
          <City>{$shipperCity}</City>
          <PostalCode>{$shipperPostalCode}</PostalCode>
          <CountryCode>{$shipperCountryCode}</CountryCode>
          <StateProvinceCode>{$shipperStateProvince}</StateProvinceCode>
      </Address>
    </Shipper>
    
    <ShipTo>
      <Address>
          <PostalCode>{$params['19_destPostal']}</PostalCode>
          <CountryCode>{$params['22_destCountry']}</CountryCode>
          <ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

        if ($params['49_residential'] === '01') {
            $xmlParams .= "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>";
        }

        $xmlParams .= <<<XMLRequest
      </Address>
    </ShipTo>
    
    <ShipFrom>
      <Address>
          <PostalCode>{$params['15_origPostal']}</PostalCode>
          <CountryCode>{$params['14_origCountry']}</CountryCode>
          <StateProvinceCode>{$params['origRegionCode']}</StateProvinceCode>
      </Address>
    </ShipFrom>

    <Package>
      <PackagingType>
        <Code>{$params['48_container']}</Code>
      </PackagingType>
      <PackageWeight>
        <UnitOfMeasurement>
          <Code>{$rowRequest->getUnitMeasure()}</Code>
        </UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
XMLRequest;
//AITOC CUSTOMIZATION
        if ($dimensions['length'] && $dimensions['width'] && $dimensions['height']) {
            if (strtolower($rowRequest->getUnitMeasure()) !== 'kgs') {
                $dimensions['length'] /= 2.54;
                $dimensions['width'] /= 2.54;
                $dimensions['height'] /= 2.54;
                $unitOfMeasurement = 'IN';
            } else {
                $unitOfMeasurement = 'CM';
            }
            $xmlParams .= <<<XMLRequest
      <Dimensions>
        <UnitOfMeasurement>
          <Code>{$unitOfMeasurement}</Code>
        </UnitOfMeasurement>
        <Length>{$dimensions['length']}</Length>
        <Width>{$dimensions['width']}</Width>
        <Height>{$dimensions['height']}</Height>
      </Dimensions>
XMLRequest;
        }

        $xmlParams .= <<<XMLRequest

    </Package>
XMLRequest;
//AITOC CUSTOMIZATION END
        if ($this->getConfigFlag('negotiated_active')) {
            $xmlParams .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
        }
        if ($this->getConfigFlag('include_taxes')) {
            $xmlParams .= "<TaxInformationIndicator/>";
        }

        $xmlParams .= <<<XMLRequest
      </Shipment>
    </RatingServiceSelectionRequest>
XMLRequest;

        $xmlRequest .= $xmlParams;

        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {
            $debugData['request'] = $xmlParams;
            try {
                $client = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Framework\HTTP\ClientFactory::class)->create();
                $client->post($url, $xmlRequest);
                $xmlResponse = $client->getBody();
                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            } catch (\Throwable $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $xmlResponse = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($xmlResponse);
    }
}
