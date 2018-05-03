<?php

namespace Serfe\Shipping\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterfaceFactory;

/**
 * InstallData class
 *
 * @author Xuan Villagran <xuan@serfe.com>
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
     * Constructor
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param GroupInterfaceFactory $groupFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        GroupRepositoryInterface $groupRepository,
        GroupInterfaceFactory $groupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

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
