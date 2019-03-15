<?php

namespace Fecon\SytelineIntegration\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{

    protected $customerSetupFactory;

    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup, ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $installer]);
            $sytelineAttribute = [
                'label' => 'Is Syteline Address',
                'visible' => false,
                'required' => false,
                'type' => 'static',
                'input' => 'boolean'
            ];
            $customerSetup->addAttribute('customer_address', 'is_syteline_address', $sytelineAttribute);
            $isSytelineAddressAttribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'is_syteline_address');
            $isSytelineAddressAttribute->setData(
                'used_in_forms',
                ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
            );
            $isSytelineAddressAttribute->save();
        }

        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $installer->getConnection()->addColumn(
                $installer->getTable('sales_order'),
                'syteline_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 32,
                    'comment' => 'Syteline Id'
                ]
            );
        }

        $installer->endSetup();
    }
}