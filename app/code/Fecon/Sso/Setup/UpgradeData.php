<?php

namespace Fecon\Sso\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    protected $customerSetupFactory;

    protected $eavSetupFactory;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
    CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
    ModuleDataSetupInterface $setup, ModuleContextInterface $context
    )
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), "1.0.2", "<")) {

            $customerSetup->addAttribute(\Magento\Customer\Model\Customer::ENTITY, 'sso_customer_group', [
                'type' => 'varchar',
                'label' => 'Documoto Customer Group',
                'input' => 'multiselect',
                'source' => 'Fecon\Sso\Model\Customer\Attribute\Source\SsoCustomerGroup',
                'required' => false,
                'visible' => true,
                'position' => 333,
                'system' => false,
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'sso_customer_group')
                ->addData(['used_in_forms' => [
                    'adminhtml_customer'
                ]
            ]);
            $attribute->save();




            $customerEntityType = $eavSetup->getEntityTypeId(\Magento\Customer\Model\Customer::ENTITY);
            $eavSetup->updateAttribute(
                $customerEntityType, 'organization', 'backend_type', 'int'
            );
            $eavSetup->updateAttribute(
                $customerEntityType, 'organization', 'source_model', 'Fecon\Sso\Model\Organization\Options'
            );
            $eavSetup->updateAttribute(
                $customerEntityType, 'organization', 'frontend_input', 'select'
            );
        }
    }
}