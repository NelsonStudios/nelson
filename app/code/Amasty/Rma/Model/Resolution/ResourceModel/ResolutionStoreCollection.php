<?php

namespace Amasty\Rma\Model\Resolution\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ResolutionStoreCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Resolution\ResolutionStore::class,
            \Amasty\Rma\Model\Resolution\ResourceModel\ResolutionStore::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
