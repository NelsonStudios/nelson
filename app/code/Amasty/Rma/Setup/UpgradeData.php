<?php

namespace Amasty\Rma\Setup;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Operation\UpgradeDataTo200
     */
    private $upgradeDataTo200;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Operation\UpgradeDataTo220
     */
    private $upgradeDataTo220;

    public function __construct(
        Operation\UpgradeDataTo200 $upgradeDataTo200,
        Operation\UpgradeDataTo220 $upgradeDataTo220,
        State $appState
    ) {
        $this->upgradeDataTo200 = $upgradeDataTo200;
        $this->upgradeDataTo220 = $upgradeDataTo220;
        $this->appState = $appState;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @return void
     * @throws \Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion() || version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->appState->emulateAreaCode(
                Area::AREA_ADMINHTML,
                [$this->upgradeDataTo200, 'execute'],
                [$setup, $context->getVersion()]
            );
        }

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->appState->emulateAreaCode(
                Area::AREA_ADMINHTML,
                [$this->upgradeDataTo220, 'execute'],
                [$setup, $context->getVersion()]
            );
        }
    }
}
