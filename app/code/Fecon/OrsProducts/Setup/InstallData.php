<?php

namespace Fecon\OrsProducts\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{

    /**
     * @var EavSetupFactory 
     */
    protected $eavSetupFactory;

    /**
     * @var \Fecon\OrsProducts\Model\Ors\AttributeSet 
     */
    protected $orsAttributeSet;

    /**
     * @var \Fecon\OrsProducts\Model\Ors\Attributes 
     */
    protected $orsAttributes;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param \Fecon\OrsProducts\Model\Ors\AttributeSet $orsAttributeSet
     * @param \Fecon\OrsProducts\Model\Ors\Attributes $orsAttributes
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Fecon\OrsProducts\Model\Ors\AttributeSet $orsAttributeSet,
        \Fecon\OrsProducts\Model\Ors\Attributes $orsAttributes
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->orsAttributeSet = $orsAttributeSet;
        $this->orsAttributes = $orsAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        /* @var $eavSetup EavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $this->orsAttributes->createAttributes($eavSetup);
        $this->orsAttributeSet->createStructure($eavSetup);
    }
}