<?php

namespace Fecon\Sso\Model\Organization;

use Fecon\Sso\Api\OrganizationRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Get Organization options
 */
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * @var null|array
     */
    protected $options;

    /**
     * @var OrganizationRepositoryInterface
     */
    protected $organizationRepository;

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
     * @param OrganizationRepositoryInterface $organizationRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        OrganizationRepositoryInterface $organizationRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array|null
     */
    public function getAllOptions()
    {
        $this->options = [
            [
                'value' => '',
                'label' => '-- Select Organization --'
            ]
        ];
        try {
            $filter =  $this->filterBuilder->setField('organization_id')
                ->setValue('%')
                ->setConditionType('like')
                ->create();
            $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
            $organizationResult = $this->organizationRepository->getList($searchCriteria);
            $organizationList = $organizationResult->getItems();
            foreach ($organizationList as $organization) {
                $this->options[] = [
                    'value' => $organization->getOrganizationId(),
                    'label' => $organization->getName()
                ];
            }
        } catch (\Exception $ex) { }

        return $this->options;
    }
}
