<?php


namespace Fecon\Sso\Model\ResourceModel;

class UserGroup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fecon_sso_usergroup', 'usergroup_id');
    }
}
