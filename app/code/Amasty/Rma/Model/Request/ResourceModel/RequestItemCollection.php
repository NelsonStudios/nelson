<?php

namespace Amasty\Rma\Model\Request\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class RequestItemCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Request\RequestItem::class,
            \Amasty\Rma\Model\Request\ResourceModel\RequestItem::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
