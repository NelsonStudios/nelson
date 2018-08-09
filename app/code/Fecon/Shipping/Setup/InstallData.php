<?php

namespace Fecon\Shipping\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\Customer;

/**
 * InstallData class
 *
 * 
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer Setup Factory
     *
     * @var CustomerSetupFactory 
     */
    protected $customerSetupFactory;

    /**
     * Customer Group Repository
     *
     * @var GroupRepositoryInterface 
     */
    protected $groupRepository;

    /**
     * Customer Group Factory
     *
     * @var GroupInterfaceFactory 
     */
    protected $groupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;

    /**
     * Constructor
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'order_token', [
            'type' => 'text',
            'label' => 'Order Token',
            'input' => 'text',
            'source' => '',
            'required' => false,
            'visible' => false,
            'position' => 300,
            'system' => false,
            'backend' => ''
        ]);

        $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'order_token_created_at', [
            'type' => 'datetime',
            'label' => 'Order Token Created At',
            'input' => 'date',
            'source' => '',
            'required' => false,
            'visible' => false,
            'position' => 600,
            'system' => false,
            'backend' => ''
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'order_token')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId
        ]);
         
        $attribute->save();
    
        $secondAttribute = $customerSetup->getEavConfig()->getAttribute(\Magento\Customer\Model\Customer::ENTITY, 'order_token_created_at')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId
        ]);
        $secondAttribute->save();
        $this->addCustomerGroup();
    }

    /**
     * Create new Customer Group
     *
     * @return void
     */
    protected function addCustomerGroup()
    {
        $customerGroup = $this->groupFactory->create();
        $customerGroup->setCode('Auto Registered');
        $customerGroup->setTaxClassId(3);
        $this->groupRepository->save($customerGroup);
    }
}
