<?php

namespace Amasty\Rma\Model\Status\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class StatusStoreCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Status\StatusStore::class,
            \Amasty\Rma\Model\Status\ResourceModel\StatusStore::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
