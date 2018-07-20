<?php

namespace Serfe\AskAnExpert\Model\ResourceModel;

class Contact extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor 
     * 
     * @return [type] [description]
     */
    protected function _construct()
    {
        $this->_init('serfe_askanexpert', 'contact_id');
    }
}
