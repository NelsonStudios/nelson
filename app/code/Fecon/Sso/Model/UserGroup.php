<?php


namespace Fecon\Sso\Model;

use Fecon\Sso\Api\Data\UserGroupInterface;
use Fecon\Sso\Api\Data\UserGroupInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class UserGroup extends \Magento\Framework\Model\AbstractModel
{

    protected $usergroupDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'fecon_sso_usergroup';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param UserGroupInterfaceFactory $usergroupDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Fecon\Sso\Model\ResourceModel\UserGroup $resource
     * @param \Fecon\Sso\Model\ResourceModel\UserGroup\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        UserGroupInterfaceFactory $usergroupDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Fecon\Sso\Model\ResourceModel\UserGroup $resource,
        \Fecon\Sso\Model\ResourceModel\UserGroup\Collection $resourceCollection,
        array $data = []
    ) {
        $this->usergroupDataFactory = $usergroupDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve usergroup model with usergroup data
     * @return UserGroupInterface
     */
    public function getDataModel()
    {
        $usergroupData = $this->getData();
        
        $usergroupDataObject = $this->usergroupDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $usergroupDataObject,
            $usergroupData,
            UserGroupInterface::class
        );
        
        return $usergroupDataObject;
    }
}
