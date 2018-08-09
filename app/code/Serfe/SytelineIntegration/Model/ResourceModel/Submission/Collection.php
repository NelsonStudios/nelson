<?php


namespace Fecon\SytelineIntegration\Model\ResourceModel\Submission;

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
            'Fecon\SytelineIntegration\Model\Submission',
            'Fecon\SytelineIntegration\Model\ResourceModel\Submission'
        );
    }
}
