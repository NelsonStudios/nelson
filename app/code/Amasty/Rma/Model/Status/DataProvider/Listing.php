<?php

namespace Amasty\Rma\Model\Status\DataProvider;

use Amasty\Rma\Model\Status\ResourceModel\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Listing extends AbstractDataProvider
{
    public function __construct(
        CollectionFactory $collectionFactory,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create()->addNotDeletedFilter();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
}
