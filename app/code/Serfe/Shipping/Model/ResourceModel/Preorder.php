<?php

namespace Serfe\Shipping\Model\ResourceModel;

class Preorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Serfe\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Serfe\Shipping\Helper\CustomerHelper $customerHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Serfe\Shipping\Helper\CustomerHelper $customerHelper,
        $connectionName = null
    ) {
        $this->customerHelper = $customerHelper;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('serfe_shipping_preorder', 'preorder_id');
    }

    /**
     * {@inheritDoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->dataHasChangedFor(\Serfe\Shipping\Api\Data\PreorderInterface::SHIPPING_PRICE)) {
            $object->setData(\Serfe\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE, 1);
            $this->customerHelper->addOrderTokenToCustomer($object->getCustomerId());
        }

        parent::_beforeSave($object);
    }
}