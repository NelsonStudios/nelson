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
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Attribute\GroupRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

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
     * @var GroupRepository
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
     protected $searchCriteriaBuilder;

    /**
     * Constructor
     *
     * @param CustomerSetupFactory $customerSetupFactory
     * @param EavSetupFactory $eavSetupFactory
     * @param ImportUserGroup $userGroupImporter
     * @param ImportOrganization $organizationImporter
     * @param GroupRepository $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        EavSetupFactory $eavSetupFactory,
        ImportUserGroup $userGroupImporter,
        ImportOrganization $organizationImporter,
        GroupRepository $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->userGroupImporter = $userGroupImporter;
        $this->organizationImporter = $organizationImporter;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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

        if (version_compare($context->getVersion(), "1.0.5", "<")) {
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'syteline_customer_id'
            );
            $eavSetup->removeAttribute(
                \Magento\Customer\Model\Customer::ENTITY,
                'customer_id'
            );
        }

        if (version_compare($context->getVersion(), "1.0.6", "<")) {
            $searchCriteria = $this->searchCriteriaBuilder
                                ->addFilter('attribute_group_code', 'ors-atributes', 'eq')->create();

            $group = $this->groupRepository->getList($searchCriteria)->getItems();

            if ($group) {
                $groupId = reset($group)->getAttributeGroupId();
                $groupWithWrongName = $this->groupRepository->get($groupId);
                $groupWithWrongName->setAttributeGroupName('ORS Attributes');
                $groupWithWrongName->setAttributeGroupCode('ors-attributes');
                $this->groupRepository->save($groupWithWrongName);
            }
        }

        if (version_compare($context->getVersion(), "1.0.7", "<")) {
            $attributeCode = 'price_list';
            $eavSetup->removeAttribute(Product::ENTITY, $attributeCode);

            $eavSetup->addAttribute(
                Product::ENTITY,
                $attributeCode,
                [
                    'type' => 'decimal',
                    'label' => 'List Price',
                    'input' => 'price',
                    'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Price::class,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => null,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'apply_to' => '',
                    'system' => 1,
                    'group' => 'ORS Attributes',
                ]
            );
        }
    }
}
