<?php


namespace Fecon\Sso\Model;

use Fecon\Sso\Api\UserGroupRepositoryInterface;
use Fecon\Sso\Api\Data\UserGroupSearchResultsInterfaceFactory;
use Fecon\Sso\Api\Data\UserGroupInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Fecon\Sso\Model\ResourceModel\UserGroup as ResourceUserGroup;
use Fecon\Sso\Model\ResourceModel\UserGroup\CollectionFactory as UserGroupCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class UserGroupRepository implements UserGroupRepositoryInterface
{

    protected $resource;

    protected $userGroupFactory;

    protected $userGroupCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataUserGroupFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceUserGroup $resource
     * @param UserGroupFactory $userGroupFactory
     * @param UserGroupInterfaceFactory $dataUserGroupFactory
     * @param UserGroupCollectionFactory $userGroupCollectionFactory
     * @param UserGroupSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceUserGroup $resource,
        UserGroupFactory $userGroupFactory,
        UserGroupInterfaceFactory $dataUserGroupFactory,
        UserGroupCollectionFactory $userGroupCollectionFactory,
        UserGroupSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->userGroupFactory = $userGroupFactory;
        $this->userGroupCollectionFactory = $userGroupCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataUserGroupFactory = $dataUserGroupFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Fecon\Sso\Api\Data\UserGroupInterface $userGroup
    ) {
        /* if (empty($userGroup->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $userGroup->setStoreId($storeId);
        } */
        
        $userGroupData = $this->extensibleDataObjectConverter->toNestedArray(
            $userGroup,
            [],
            \Fecon\Sso\Api\Data\UserGroupInterface::class
        );
        
        $userGroupModel = $this->userGroupFactory->create()->setData($userGroupData);
        
        try {
            $this->resource->save($userGroupModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the userGroup: %1',
                $exception->getMessage()
            ));
        }
        return $userGroupModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($userGroupId)
    {
        $userGroup = $this->userGroupFactory->create();
        $this->resource->load($userGroup, $userGroupId);
        if (!$userGroup->getId()) {
            throw new NoSuchEntityException(__('UserGroup with id "%1" does not exist.', $userGroupId));
        }
        return $userGroup->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->userGroupCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Fecon\Sso\Api\Data\UserGroupInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Fecon\Sso\Api\Data\UserGroupInterface $userGroup
    ) {
        try {
            $userGroupModel = $this->userGroupFactory->create();
            $this->resource->load($userGroupModel, $userGroup->getUsergroupId());
            $this->resource->delete($userGroupModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the UserGroup: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($userGroupId)
    {
        return $this->delete($this->getById($userGroupId));
    }
}
