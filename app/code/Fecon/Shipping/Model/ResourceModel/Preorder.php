<?php

namespace Fecon\Shipping\Model\ResourceModel;

use Fecon\Shipping\Api\Data\PreorderInterface;

class Preorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Fecon\Shipping\Helper\CustomerHelper 
     */
    protected $customerHelper;

    /**
     * Email Helper
     *
     * @var \Fecon\Shipping\Helper\EmailHelper 
     */
    protected $emailHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Fecon\Shipping\Helper\CustomerHelper $customerHelper
     * @param \Fecon\Shipping\Helper\EmailHelper $emailHelper
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Fecon\Shipping\Helper\CustomerHelper $customerHelper,
        \Fecon\Shipping\Helper\EmailHelper $emailHelper,
        $connectionName = null
    ) {
        $this->customerHelper = $customerHelper;
        $this->emailHelper = $emailHelper;
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
        $status = (int) $object->getData(PreorderInterface::STATUS);
        if (
            $object->dataHasChangedFor(PreorderInterface::SHIPPING_PRICE) &&
            ($status === PreorderInterface::STATUS_NEW)
        ) {
            $object->setData(PreorderInterface::IS_AVAILABLE, 1);
            $object->setData(PreorderInterface::STATUS, PreorderInterface::STATUS_PENDING);
            $this->customerHelper->addOrderTokenToCustomer($object->getCustomerId());
        }

        parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $status = (int) $object->getData(PreorderInterface::STATUS);
        if ($status === PreorderInterface::STATUS_NEW) {
            $preorderId = $object->getData(PreorderInterface::PREORDER_ID);
            $this->emailHelper->sendAdminNotificationEmail($preorderId);
        }

        return parent::_afterSave($object);
    }
}