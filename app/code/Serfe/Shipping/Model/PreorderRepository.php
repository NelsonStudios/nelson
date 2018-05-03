<?php


namespace Serfe\Shipping\Model;

use Serfe\Shipping\Api\Data\PreorderInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Serfe\Shipping\Api\PreorderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Serfe\Shipping\Model\ResourceModel\Preorder as ResourcePreorder;
use Magento\Framework\Exception\CouldNotSaveException;
use Serfe\Shipping\Api\Data\PreorderSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Serfe\Shipping\Model\ResourceModel\Preorder\CollectionFactory as PreorderCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;

class PreorderRepository implements preorderRepositoryInterface
{

    protected $preorderFactory;

    protected $resource;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    protected $dataPreorderFactory;

    protected $preorderCollectionFactory;

    protected $dataObjectHelper;


    /**
     * @param ResourcePreorder $resource
     * @param PreorderFactory $preorderFactory
     * @param PreorderInterfaceFactory $dataPreorderFactory
     * @param PreorderCollectionFactory $preorderCollectionFactory
     * @param PreorderSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourcePreorder $resource,
        PreorderFactory $preorderFactory,
        PreorderInterfaceFactory $dataPreorderFactory,
        PreorderCollectionFactory $preorderCollectionFactory,
        PreorderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->preorderFactory = $preorderFactory;
        $this->preorderCollectionFactory = $preorderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataPreorderFactory = $dataPreorderFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Serfe\Shipping\Api\Data\PreorderInterface $preorder
    ) {
        /* if (empty($preorder->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $preorder->setStoreId($storeId);
        } */
        try {
            $preorder->getResource()->save($preorder);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the preorder: %1',
                $exception->getMessage()
            ));
        }
        return $preorder;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($preorderId)
    {
        $preorder = $this->preorderFactory->create();
        $preorder->getResource()->load($preorder, $preorderId);
        if (!$preorder->getId()) {
            throw new NoSuchEntityException(__('Preorder with id "%1" does not exist.', $preorderId));
        }
        return $preorder;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->preorderCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
        
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Serfe\Shipping\Api\Data\PreorderInterface $preorder
    ) {
        try {
            $preorder->getResource()->delete($preorder);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Preorder: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($preorderId)
    {
        return $this->delete($this->getById($preorderId));
    }
}
