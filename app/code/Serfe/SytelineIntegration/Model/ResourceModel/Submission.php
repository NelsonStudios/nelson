<?php


namespace Serfe\SytelineIntegration\Model\ResourceModel;

class Submission extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('serfe_sytelineintegration_submission', 'submission_id');
    }
}
