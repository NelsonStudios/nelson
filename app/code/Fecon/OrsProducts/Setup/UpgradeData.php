<?php

namespace Fecon\OrsProducts\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;

/**
 * Description of UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeFactory $attributeFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.0.1", "<")) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE);

            $attributeCodes = ['manufacturer', 'family'];
            foreach ($attributeCodes as $attributeCode) {

                // IMPORTANT:
                // use $this->attributeFactory->create() before loading the attribute,
                // or else the options you want to delete will be cached and you cannot 
                // delete other options from a second attribute in the same request
                $attribute = $this->attributeFactory->create()->loadByCode($entityTypeId, $attributeCode);

                $options = $attribute->getOptions();

                $optionsToRemove = [];
                foreach ($options as $option) {
                    if ($option['value']) {
                        $optionsToRemove['delete'][$option['value']] = true;
                        $optionsToRemove['value'][$option['value']] = true;
                    }
                }
                $eavSetup->addAttributeOption($optionsToRemove);
            }
        }
    }
}