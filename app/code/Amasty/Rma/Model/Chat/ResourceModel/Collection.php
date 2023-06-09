<?php

namespace Amasty\Rma\Model\Chat\ResourceModel;

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
            \Amasty\Rma\Model\Chat\Message::class,
            \Amasty\Rma\Model\Chat\ResourceModel\Message::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
