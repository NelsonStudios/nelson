<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2021 Aitoc (https://www.aitoc.com)
 * @package Aitoc_DimensionalShipping
 */


namespace Aitoc\DimensionalShipping\Model\Rewrite\Usps;

use Aitoc\DimensionalShipping\Plugin\Shipping;
use Magento\Usps\Model\Carrier as UspsCarrier;

class Carrier extends UspsCarrier
{
    protected function _getQuotes()
    {
        $r = $this->_rawRequest;
        $oldSize = $r->getSize();

        $dimensionData = $this->_request->getData(Shipping::DIMENSIONS);
        if (!empty($dimensionData)) {
            $dimensions = array_shift($dimensionData);
            $this->_request->setData(Shipping::DIMENSIONS, $dimensionData);
            if ($dimensions['length'] && $dimensions['width'] && $dimensions['height']) {
                $r->setSize('LARGE');
                $r->setWidth($dimensions['width'] / 2.54);
                $r->setHeight($dimensions['height'] / 2.54);
                $r->setLength($dimensions['length'] / 2.54);
            }
        }

        $result = $this->_getXmlQuotes();

        $r->setSize($oldSize);
        return $result;
    }
}
