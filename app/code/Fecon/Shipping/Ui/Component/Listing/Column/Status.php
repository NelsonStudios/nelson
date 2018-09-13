<?php

namespace Fecon\Shipping\Ui\Component\Listing\Column;

use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Status data source
 */
class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    const STATUS_NEW = 'NEW';
    const STATUS_PENDING = 'PENDING';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_CANCELED = 'CANCELED';

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
                $statusRaw = $item[PreorderInterface::STATUS];
                switch ($statusRaw) {
                    case PreorderInterface::STATUS_NEW:
                        $status = self::STATUS_NEW;
                        break;
                    case PreorderInterface::STATUS_PENDING:
                        $status = self::STATUS_PENDING;
                        break;
                    case PreorderInterface::STATUS_COMPLETED:
                        $status = self::STATUS_COMPLETED;
                        break;
                    case PreorderInterface::STATUS_CANCELED:
                        $status = self::STATUS_CANCELED;
                        break;
                }
                $item[$this->getData('name')] = $status;
            }
        }

        return $dataSource;
    }
}