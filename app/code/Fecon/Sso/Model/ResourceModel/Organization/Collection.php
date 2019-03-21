<?php


namespace Fecon\Sso\Model\ResourceModel\Organization;

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
            \Fecon\Sso\Model\Organization::class,
            \Fecon\Sso\Model\ResourceModel\Organization::class
        );
    }
}
