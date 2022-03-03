<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model\ResourceModel\Customers;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'elsnertech_paytrace_customers_collection';
    protected $_eventObject = 'paytrace_customers_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Elsnertech\Paytrace\Model\Customers::class,
            \Elsnertech\Paytrace\Model\ResourceModel\Customers::class
        );
    }
}
