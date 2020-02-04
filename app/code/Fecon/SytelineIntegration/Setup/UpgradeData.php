<?php

namespace Fecon\SytelineIntegration\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    protected $customerSetupFactory;
    private $salesSetupFactory;
    private $quoteSetupFactory;

    public function __construct(
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
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
        if (version_compare($context->getVersion(), "1.1.0", "<")) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $quoteSetup->addAttribute('quote', 'syteline_checkout_extra_fields',
                [
                    'type' => 'text',
                    'length' => null,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
            $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
            $salesSetup->addAttribute('order', 'syteline_checkout_extra_fields',
                [
                    'type' => 'text',
                    'length' => null,
                    'visible' => false,
                    'required' => false,
                    'grid' => false
                ]
            );
        }

        $installer->endSetup();
    }
}