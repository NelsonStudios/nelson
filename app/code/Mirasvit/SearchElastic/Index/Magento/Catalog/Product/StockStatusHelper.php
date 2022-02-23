<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-elastic
 * @version   1.2.75
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchElastic\Index\Magento\Catalog\Product;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\CompatibilityService;

class StockStatusHelper
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var mixed
     */
    private $stockResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var mixed
     */
    private $getSkusByProductIds;

    /**
     * @var int
     */
    private $stockId = Stock::DEFAULT_STOCK_ID;

    /**
     * @var string
     */
    private $sourceCodes;

    /**
     * @var bool
     */
    private $showOutOfStock;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var bool
     */
    private static $multiSourceInventorySupported = false;

    /**
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection    $resource
     * @param Manager               $moduleManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ResourceConnection $resource,
        Manager $moduleManager
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->storeManager  = $storeManager;
        $this->resource      = $resource;
        $this->moduleManager = $moduleManager;

        if (!self::$multiSourceInventorySupported) {
            self::$multiSourceInventorySupported = CompatibilityService::is23() &&
                $this->moduleManager->isOutputEnabled('Magento_InventorySales') &&
                $this->moduleManager->isOutputEnabled('Magento_Inventory');
        }

        if (self::$multiSourceInventorySupported) {
            $this->stockResolver       = CompatibilityService::getObjectManager()
                ->create(\Magento\InventorySales\Model\StockResolver::class);
            $this->getSkusByProductIds = CompatibilityService::getObjectManager()
                ->create('Magento\InventoryCatalog\Model\GetSkusByProductIds');
        }
    }

    /**
     * @param integer $scopeId
     *
     * @return void
     */
    public function init($scopeId = null)
    {
        $this->showOutOfStock = $this->scopeConfig->getValue(
            'cataloginventory/options/show_out_of_stock'
        );

        if (self::$multiSourceInventorySupported) {
            $websiteId   = $this->storeManager->getStore($scopeId)->getWebsiteId();
            $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();

            $this->stockId = $this->stockResolver->execute(
                \Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE,
                $websiteCode
            )->getStockId();

            $sourceCodes = $this->getSourceCodes($this->stockId);
            array_push($sourceCodes, 'default');
            $this->sourceCodes = "'" . implode("','", $sourceCodes) . "'";
        }
    }

    /**
     * @param integer $productId
     * @param bool    $actual
     *
     * @return int Product stock status
     */
    public function getProductStockStatus($productId, $actual = false)
    {
        if ($this->showOutOfStock && !$actual) {
            return 1;
        }

        $connection = $this->resource->getConnection();
        $select = false;

        if (self::$multiSourceInventorySupported) {
            $sku    = $this->getSkusByProductIds->execute([$productId])[$productId];
            $table  = $this->resource->getTableName('inventory_source_item');
            $select = $connection->select()
                ->from($table, ['MAX(status)'])
                ->where('sku = ? AND source_code IN (' . $this->sourceCodes . ')', $sku);
        }

        try {
            if (!$select || ($select && (int) $connection->fetchOne($select) === 0)) {
                $select = $connection->select()
                    ->from($this->resource->getTableName('cataloginventory_stock_status'), ['stock_status'])
                    ->where('product_id = ?', (int)$productId);
            }

            return (int)$connection->fetchOne($select);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * @param int $stockId
     * @return array
     */
    private function getSourceCodes($stockId)
    {
        $connection = $this->resource->getConnection();
        $select     = $connection->select()
            ->from(
                $this->resource->getTableName('inventory_source_stock_link'),
                ['source_code']
            )
            ->where('stock_id = ?', $stockId);

        return array_column($connection->fetchAll($select), 'source_code');
    }
}
