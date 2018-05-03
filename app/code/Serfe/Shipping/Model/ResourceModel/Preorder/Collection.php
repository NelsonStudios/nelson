<?php


namespace Serfe\Shipping\Model\ResourceModel\Preorder;

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
            'Serfe\Shipping\Model\Preorder',
            'Serfe\Shipping\Model\ResourceModel\Preorder'
        );
    }
}
