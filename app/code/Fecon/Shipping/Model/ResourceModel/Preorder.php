<?php

namespace Fecon\Shipping\Model\ResourceModel;

class Preorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Fecon\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Fecon\Shipping\Helper\CustomerHelper $customerHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Fecon\Shipping\Helper\CustomerHelper $customerHelper,
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
        $this->_init('fecon_shipping_preorder', 'preorder_id');
    }

    /**
     * {@inheritDoc}
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->dataHasChangedFor(\Fecon\Shipping\Api\Data\PreorderInterface::SHIPPING_PRICE)) {
            $object->setData(\Fecon\Shipping\Api\Data\PreorderInterface::IS_AVAILABLE, 1);
            $this->customerHelper->addOrderTokenToCustomer($object->getCustomerId());
        }

        parent::_beforeSave($object);
    }
}