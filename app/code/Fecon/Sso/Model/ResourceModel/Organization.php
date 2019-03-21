<?php


namespace Fecon\Sso\Model\ResourceModel;

class Organization extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fecon_sso_organization', 'organization_id');
    }
}
