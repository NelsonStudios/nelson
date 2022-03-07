<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model\ResourceModel;

class Customers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected function _construct()
    {
        $this->_init('elsnertech_paytrace_customers', 'entity_id');
    }

    public function getVaultCardByDetail(
        $customerId,
        $ccnumber,
        $month,
        $year,
        $cType
    ) {

        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), '*')
            ->where('customer_id = ?', $customerId)
            ->where('cc_number = ?', $ccnumber)
            ->where('cc_month = ?', $month)
            ->where('cc_year = ?', $year)
            ->where('cc_type = ?', $cType);
        $result = $adapter->fetchAll($select);
        return $result;
    }
}
