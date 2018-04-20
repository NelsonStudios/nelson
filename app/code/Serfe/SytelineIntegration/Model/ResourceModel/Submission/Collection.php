<?php


namespace Serfe\SytelineIntegration\Model\ResourceModel\Submission;

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
            'Serfe\SytelineIntegration\Model\Submission',
            'Serfe\SytelineIntegration\Model\ResourceModel\Submission'
        );
    }
}
