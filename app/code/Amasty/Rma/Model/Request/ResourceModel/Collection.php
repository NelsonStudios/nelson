<?php

namespace Amasty\Rma\Model\Request\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Request\Request::class,
            \Amasty\Rma\Model\Request\ResourceModel\Request::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
