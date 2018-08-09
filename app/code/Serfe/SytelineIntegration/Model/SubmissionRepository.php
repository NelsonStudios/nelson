<?php


namespace Fecon\SytelineIntegration\Model;

use Fecon\SytelineIntegration\Api\Data\SubmissionInterfaceFactory;
use Fecon\SytelineIntegration\Model\ResourceModel\Submission\CollectionFactory as SubmissionCollectionFactory;
use Fecon\SytelineIntegration\Api\Data\SubmissionSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Fecon\SytelineIntegration\Api\SubmissionRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Fecon\SytelineIntegration\Model\ResourceModel\Submission as ResourceSubmission;
use Magento\Framework\Exception\CouldNotSaveException;

class SubmissionRepository implements submissionRepositoryInterface
{

    protected $submissionFactory;

    protected $dataSubmissionFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    protected $submissionCollectionFactory;

    protected $dataObjectHelper;

    protected $resource;


    /**
     * @param ResourceSubmission $resource
     * @param SubmissionFactory $submissionFactory
     * @param SubmissionInterfaceFactory $dataSubmissionFactory
     * @param SubmissionCollectionFactory $submissionCollectionFactory
     * @param SubmissionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceSubmission $resource,
        SubmissionFactory $submissionFactory,
        SubmissionInterfaceFactory $dataSubmissionFactory,
        SubmissionCollectionFactory $submissionCollectionFactory,
        SubmissionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->submissionFactory = $submissionFactory;
        $this->submissionCollectionFactory = $submissionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataSubmissionFactory = $dataSubmissionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Fecon\SytelineIntegration\Api\Data\SubmissionInterface $submission
    ) {
        /* if (empty($submission->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $submission->setStoreId($storeId);
        } */
        try {
            $submission->getResource()->save($submission);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the submission: %1',
                $exception->getMessage()
            ));
        }
        return $submission;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($submissionId)
    {
        $submission = $this->submissionFactory->create();
        $submission->getResource()->load($submission, $submissionId);
        if (!$submission->getId()) {
            throw new NoSuchEntityException(__('Submission with id "%1" does not exist.', $submissionId));
        }
        return $submission;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->submissionCollectionFactory->create();
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
        \Fecon\SytelineIntegration\Api\Data\SubmissionInterface $submission
    ) {
        try {
            $submission->getResource()->delete($submission);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Submission: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($submissionId)
    {
        return $this->delete($this->getById($submissionId));
    }
}
