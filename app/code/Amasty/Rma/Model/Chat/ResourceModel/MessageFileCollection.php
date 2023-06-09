<?php

namespace Amasty\Rma\Model\Chat\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MessageFileCollection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Rma\Model\Chat\MessageFile::class,
            \Amasty\Rma\Model\Chat\ResourceModel\MessageFile::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
