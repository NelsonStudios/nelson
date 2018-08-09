<?php


namespace Fecon\Shipping\Model\ResourceModel\Preorder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Fecon\Shipping\Model\Preorder',
            'Fecon\Shipping\Model\ResourceModel\Preorder'
        );
    }
}
