<?php


namespace Fecon\SytelineIntegration\Model\ResourceModel;

class Submission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fecon_sytelineintegration_submission', 'submission_id');
    }
}
