<?php


namespace Serfe\Shipping\Model\ResourceModel;

class Preorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('serfe_shipping_preorder', 'preorder_id');
    }
}
