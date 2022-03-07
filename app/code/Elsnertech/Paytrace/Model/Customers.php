<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model;

class Customers extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Elsnertech\Paytrace\Model\ResourceModel\Customers::class);
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Elsnertech\Paytrace\Model\Api\Config $config,
        array $data = []
    ) {
        $this->_config = $config;
        parent::__construct($context, $registry);
    }

    public function getVaultCardByDetail(
        $paytraceid,
        $customerId,
        $ccnumber,
        $month,
        $year,
        $cType
    ) {

        $data = $this->_getResource()->getVaultCardByDetail(
            $customerId,
            $ccnumber,
            $month,
            $year,
            $cType
        );
        $entityId = 0;
        if (empty($data) !== true) {
            foreach ($data as $key => $value) {
                if ($this->_config->decryptText($value['paytrace_customer_id']) == $paytraceid) {
                    $entityId = $value['entity_id'];
                }
            }
        }
        if ($entityId) {
            $this->load($entityId);
        }
        return $this;
    }
}
