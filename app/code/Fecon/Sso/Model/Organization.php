<?php


namespace Fecon\Sso\Model;

use Fecon\Sso\Api\Data\OrganizationInterface;
use Fecon\Sso\Api\Data\OrganizationInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Organization extends \Magento\Framework\Model\AbstractModel
{

    protected $organizationDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'fecon_sso_organization';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param OrganizationInterfaceFactory $organizationDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Fecon\Sso\Model\ResourceModel\Organization $resource
     * @param \Fecon\Sso\Model\ResourceModel\Organization\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        OrganizationInterfaceFactory $organizationDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Fecon\Sso\Model\ResourceModel\Organization $resource,
        \Fecon\Sso\Model\ResourceModel\Organization\Collection $resourceCollection,
        array $data = []
    ) {
        $this->organizationDataFactory = $organizationDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve organization model with organization data
     * @return OrganizationInterface
     */
    public function getDataModel()
    {
        $organizationData = $this->getData();
        
        $organizationDataObject = $this->organizationDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $organizationDataObject,
            $organizationData,
            OrganizationInterface::class
        );
        
        return $organizationDataObject;
    }
}
