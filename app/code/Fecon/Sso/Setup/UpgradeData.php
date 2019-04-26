<?php

namespace Fecon\Sso\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Fecon\Sso\Import\ImportUserGroup;
use Fecon\Sso\Import\ImportOrganization;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ImportOrganization
     */
    protected $organizationImporter;

    /**
     * @var ImportUserGroup
     */
    protected $userGroupImporter;

    /**
     * Constructor
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param ImportUserGroup $userGroupImporter
     * @param ImportOrganization $organizationImporter
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory,
        ImportUserGroup $userGroupImporter,
        ImportOrganization $organizationImporter
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->userGroupImporter = $userGroupImporter;
        $this->organizationImporter = $organizationImporter;
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

        if (version_compare($context->getVersion(), "1.0.3", "<")) {
            $this->organizationImporter->importOrganizations('organizations.csv', true);
            $this->userGroupImporter->importUserGroups('user-groups.csv', true);
        }

        if (version_compare($context->getVersion(), "1.0.4", "<")) {
            // Set is_documoto_user as true by default
            $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'is_documoto_user')
                ->setData('default_value', 1);
            $attribute->save();
        }
    }
}
