<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model\Rewrite\Fedex;

use Magento\Fedex\Model\Carrier as FedexCarrier;
use Magento\Framework\App\ObjectManager;

class Carrier extends FedexCarrier
{
    /**
     * @var array|null
     */
    private $parcels;

    /**
     * @inheritDoc
     */
    protected function _formRateRequest($purpose)
    {
        $r = $this->_rawRequest;
        $ratesRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => ['Key' => $r->getKey(), 'Password' => $r->getPassword()],
            ],
            'ClientDetail' => ['AccountNumber' => $r->getAccount(), 'MeterNumber' => $r->getMeterNumber()],
            'Version' => $this->getVersionInfo(),
            'RequestedShipment' => [
                'DropoffType' => $r->getDropoffType(),
                'ShipTimestamp' => date('c'),
                'PackagingType' => $r->getPackaging(),
                'TotalInsuredValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                'Shipper' => [
                    'Address' => ['PostalCode' => $r->getOrigPostal(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'Recipient' => [
                    'Address' => [
                        'PostalCode' => $r->getDestPostal(),
                        'CountryCode' => $r->getDestCountry(),
                        'Residential' => (bool)$this->getConfigData('residence_delivery'),
                    ],
                ],
                'ShippingChargesPayment' => [
                    'PaymentType' => 'SENDER',
                    'Payor' => ['AccountNumber' => $r->getAccount(), 'CountryCode' => $r->getOrigCountry()],
                ],
                'CustomsClearanceDetail' => [
                    'CustomsValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                ],
                'RateRequestTypes' => 'LIST',
                'PackageCount' => '1',
                'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                'RequestedPackageLineItems' => [
                    '0' => [
                        'Weight' => [
                            'Value' => (double)$r->getWeight(),
                            'Units' => $this->getConfigData('unit_of_measure'),
                        ],
                        'GroupPackageCount' => 1,
                    ],
                ],
            ],
        ];

        if ($r->getDestCity()) {
            $ratesRequest['RequestedShipment']['Recipient']['Address']['City'] = $r->getDestCity();
        }

        if ($purpose == self::RATE_REQUEST_GENERAL) {
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = [
                'Amount' => $r->getValue(),
                'Currency' => $this->getCurrencyCode(),
            ];
        } else {
            if ($purpose == self::RATE_REQUEST_SMARTPOST) {
                $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
                $ratesRequest['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => (double)$r->getWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                    'HubId' => $this->getConfigData('smartpost_hubid'),
                ];
            }
        }

//AITOC CUSTOMIZATION
        if ($this->getParcels()) {
            $packageCount = count($this->getParcels());
            $ratesRequest['RequestedShipment']['RequestedPackageLineItems'] = [];
            foreach ($this->getParcels() as $parcel) {
                $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][] = $parcel;

            }
            $ratesRequest['RequestedShipment']['PackageCount'] = $packageCount;

            if ($purpose == self::RATE_REQUEST_GENERAL) {
                $amount = $r->getValue() / $packageCount;
                for ($i = 0; $i < $packageCount; $i++) {
                    $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][$i]['InsuredValue'] = [
                        'Amount' => $amount,
                        'Currency' => $this->getCurrencyCode(),
                    ];
                }
            }
        }
//AITOC CUSTOMIZATION END

        return $ratesRequest;
    }

    /**
     * @return \Generator
     */
    protected function getParcels()
    {
        if ($this->parcels === null) {
            $this->parcels = [];
            $packer = ObjectManager::getInstance()->get(\Aitoc\DimensionalShipping\Model\Packer::class);
            if ($packer->getBoxes()) {
                $unpackedWeight = 0;
                foreach ($packer->getUnpackedItems() as $item) {
                    $unpackedWeight += $item->getWeight() * $item->getQty();
                }
                if ($unpackedWeight) {
                    $parcel = [
                        'Weight' => [
                            'Value' => (double)$unpackedWeight,
                            'Units' => $this->getConfigData('unit_of_measure'),
                        ],
                        'GroupPackageCount' => 1
                    ];
                    $this->parcels[] = $parcel;
                }

                foreach ($packer->getBoxes() as $packedBox) {
                    $dimensions = $packer->getCmDimensionsByBox($packedBox);
                    $weightUnit = $this->getConfigData('unit_of_measure');
                    if (strtolower($weightUnit) !== 'kgs') {
                        $dimensions['length'] /= 2.54;
                        $dimensions['width'] /= 2.54;
                        $dimensions['height'] /= 2.54;
                        $unitOfMeasurement = 'IN';
                    } else {
                        $unitOfMeasurement = 'CM';
                    }
                    $parcel = [
                        'Weight' => [
                            'Value' => $packedBox->getWeight(),
                            'Units' => $weightUnit
                        ],
                        'Dimensions' => [
                            'Length' => $dimensions['length'],
                            'Width' => $dimensions['width'],
                            'Height' => $dimensions['height'],
                            'Units' => $unitOfMeasurement
                        ],
                        'GroupPackageCount' => 1,
                    ];

                    $this->parcels[] = $parcel;
                }
            }
        }

        return $this->parcels;
    }

    /**
     * @inheritDoc
     */
    public function getTotalNumOfBoxes($weight)
    {
        $this->_numBoxes = count($this->getParcels());
        return $weight;
    }


    /**
     * @inheritDoc
     */
    protected function _getPerorderPrice($cost, $handlingType, $handlingFee)
    {
        if ($handlingType == self::HANDLING_TYPE_PERCENT) {
            return $cost + $cost * $handlingFee / 100;
        }

        return $cost + $handlingFee;
    }
}
