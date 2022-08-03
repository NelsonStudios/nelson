<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Plugin;

class Shipping
{
    const DIMENSIONS = 'aitoc_carrier_package_dimensions';

    /**
     * @var array
     */
    private $realDimensionCarriers = ['ups', 'usps'];

    /**
     * @var \Aitoc\DimensionalShipping\Model\Packer
     */
    private $packer;

    /**
     * @inheritDoc
     */
    public function __construct(
        \Aitoc\DimensionalShipping\Model\Packer $packer
    ) {
        $this->packer = $packer;
    }

    /**
     * @param \Magento\Shipping\Model\Shipping $subject
     * @param $carrierCode
     * @param $request
     * @return array|mixed
     */
    public function beforeCollectCarrierRates(
        $subject,
        $carrierCode,
        $request
    ) {
        $this->packer->execute($request->getAllItems());
        $request->setPackageWeight(array_sum($this->packer->getWeights(true)));
        return [$carrierCode, $request];
    }

    public function aroundComposePackagesForCarrier(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        \Magento\Shipping\Model\Carrier\AbstractCarrier $carrier,
        \Magento\Quote\Model\Quote\Address\RateRequest $request
    ) {
        $weights = [];
        if (in_array($carrier->getCarrierCode(), $this->realDimensionCarriers)
            && ($carrier->getCarrierCode() != 'ups' || $carrier->getConfigData('type') == 'UPS_XML')
        ) {
            $dimensionsData = [];
            foreach ($this->packer->getBoxes() as $packedBox) {
                $weights[] = $packedBox->getWeight();
                $dimensionsData[] = $this->packer->getCmDimensionsByBox($packedBox);
            }
            $request->setData(self::DIMENSIONS, $dimensionsData);
            $unpackedWeight = 0;
            foreach ($this->packer->getUnpackedItems() as $item) {
                $unpackedWeight += $item->getWeight() * $item->getQty();
            }
            $weights[] = $unpackedWeight;
        } else {
            $weights = $this->packer->getWeights(true);
        }

        if ($weights) {
            $result = [];
            foreach ($weights as $weight) {
                if (!$weight) {
                    continue;
                }
                $weight = (string)$weight;
                if (empty($result[$weight])) {
                    $result[$weight] = 1;
                } else {
                    $result[$weight] += 1;
                }
            }
            return $result;
        }

        return $proceed($carrier, $request);
    }
}