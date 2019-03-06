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
        $setup->startSetup();
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
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
        $setup->endSetup();
    }
}