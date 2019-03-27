<?php

namespace Fecon\Sso\Model\Customer\Attribute\Source;

use Fecon\Sso\Api\UserGroupRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

class SsoCustomerGroup extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var UserGroupRepositoryInterface
     */
    protected $userGroupRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param UserGroupRepositoryInterface $userGroupRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        UserGroupRepositoryInterface $userGroupRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->userGroupRepository = $userGroupRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->_options = [];
        try {
            $filter =  $this->filterBuilder->setField('usergroup_id')
                ->setValue('%')
                ->setConditionType('like')
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
            $userGroupResult = $this->userGroupRepository->getList($searchCriteria);
            $userGroupList = $userGroupResult->getItems();
            foreach ($userGroupList as $userGroup) {
                $this->_options[] = [
                    'value' => $userGroup->getUsergroupId(),
                    'label' => $userGroup->getName()
                ];
            }
        } catch (\Exception $ex) { }

        return $this->_options;
    }
}