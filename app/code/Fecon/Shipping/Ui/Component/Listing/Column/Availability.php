<?php

namespace Fecon\Shipping\Ui\Component\Listing\Column;

/**
 * Data source for Preorder status in backend grid
 *
 * 
 */
class Availability extends \Magento\Ui\Component\Listing\Columns\Column
{
    const AVAILABLE = 'Available';
    const NOT_AVAILABLE = 'Not available';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $isAvailable = $item[\Fecon\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE];
                $availability = $isAvailable ? $this::AVAILABLE : $this::NOT_AVAILABLE;
                $item[$this->getData('name')] = $availability;
            }
        }

        return $dataSource;
    }
}