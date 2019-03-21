<?php


namespace Fecon\Sso\Model;

use Fecon\Sso\Api\OrganizationRepositoryInterface;
use Fecon\Sso\Api\Data\OrganizationSearchResultsInterfaceFactory;
use Fecon\Sso\Api\Data\OrganizationInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Fecon\Sso\Model\ResourceModel\Organization as ResourceOrganization;
use Fecon\Sso\Model\ResourceModel\Organization\CollectionFactory as OrganizationCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

class OrganizationRepository implements OrganizationRepositoryInterface
{

    protected $resource;

    protected $organizationFactory;

    protected $organizationCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataOrganizationFactory;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceOrganization $resource
     * @param OrganizationFactory $organizationFactory
     * @param OrganizationInterfaceFactory $dataOrganizationFactory
     * @param OrganizationCollectionFactory $organizationCollectionFactory
     * @param OrganizationSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceOrganization $resource,
        OrganizationFactory $organizationFactory,
        OrganizationInterfaceFactory $dataOrganizationFactory,
        OrganizationCollectionFactory $organizationCollectionFactory,
        OrganizationSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->organizationFactory = $organizationFactory;
        $this->organizationCollectionFactory = $organizationCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataOrganizationFactory = $dataOrganizationFactory;
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
        \Fecon\Sso\Api\Data\OrganizationInterface $organization
    ) {
        /* if (empty($organization->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $organization->setStoreId($storeId);
        } */
        
        $organizationData = $this->extensibleDataObjectConverter->toNestedArray(
            $organization,
            [],
            \Fecon\Sso\Api\Data\OrganizationInterface::class
        );
        
        $organizationModel = $this->organizationFactory->create()->setData($organizationData);
        
        try {
            $this->resource->save($organizationModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the organization: %1',
                $exception->getMessage()
            ));
        }
        return $organizationModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getById($organizationId)
    {
        $organization = $this->organizationFactory->create();
        $this->resource->load($organization, $organizationId);
        if (!$organization->getId()) {
            throw new NoSuchEntityException(__('Organization with id "%1" does not exist.', $organizationId));
        }
        return $organization->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->organizationCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Fecon\Sso\Api\Data\OrganizationInterface::class
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
        \Fecon\Sso\Api\Data\OrganizationInterface $organization
    ) {
        try {
            $organizationModel = $this->organizationFactory->create();
            $this->resource->load($organizationModel, $organization->getOrganizationId());
            $this->resource->delete($organizationModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Organization: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($organizationId)
    {
        return $this->delete($this->getById($organizationId));
    }
}
