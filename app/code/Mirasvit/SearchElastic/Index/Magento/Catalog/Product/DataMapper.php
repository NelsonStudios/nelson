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

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

class DataMapper implements DataMapperInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StockStatusHelper
     */
    private $stockStatusHelper;

    /**
     * @var array
     */
    static $attributeCache = [];

    /**
     * DataMapper constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     * @param EavConfig                $eavConfig
     * @param ResourceConnection       $resource
     * @param ProductMetadataInterface $productMetadata
     * @param ScopeConfigInterface     $scopeConfig
     * @param StockStatusHelper        $stockStatusHelper
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository,
        EavConfig $eavConfig,
        ResourceConnection $resource,
        ProductMetadataInterface $productMetadata,
        ScopeConfigInterface $scopeConfig,
        StockStatusHelper $stockStatusHelper
    ) {
        $this->indexRepository   = $indexRepository;
        $this->eavConfig         = $eavConfig;
        $this->resource          = $resource;
        $this->productMetadata   = $productMetadata;
        $this->scopeConfig       = $scopeConfig;
        $this->stockStatusHelper = $stockStatusHelper;
    }

    /**
     * @param array                                         $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param IndexInterface                                $index
     *
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    public function map(array $documents, $dimensions, $index)
    {
        $dimension = current($dimensions);
        $this->stockStatusHelper->init($dimension->getValue());

        $rawDocs = [];
        $priceData = [];
        foreach (['catalog_product_index_eav', 'catalog_product_entity_varchar', 'catalog_product_entity_text',
            'catalog_product_entity_decimal', 'catalog_product_index_price'] as $table) {
            $dt = $this->eavMap($table, array_keys($documents), $dimension->getValue());

            foreach ($dt as $row) {
                $entityId    = isset($row['row_id']) ? $row['row_id'] : $row['entity_id'];
                $entityId    = isset($row['parent_id']) ? $row['parent_id'] : $entityId;
                if (isset($row['attribute_id'])) {
                    $attributeId = $row['attribute_id'];
                    if (!isset(self::$attributeCache[$attributeId])) {
                        self::$attributeCache[$attributeId] = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeId);
                    }

                    $attribute = self::$attributeCache[$attributeId];

                    $rawDocs[$entityId][$attribute->getAttributeCode()][] = $row['value'];
                } else {
                    $priceData[$entityId]['price'] = [$row['min_price'], $row['max_price']];
                }
            }
        }

        if (!empty($priceData)) {
            foreach ($priceData as $id => $price) {
                $documents[$id] = array_replace($documents[$id], $price);
            }
        }

        foreach ($documents as $id => $doc) {
            if (isset($rawDocs[$id])) {
                $rawData = $rawDocs[$id];
            } else {
                continue;
            }

            $documents[$id] = $this->prepareRawData($rawData, $id, $doc);
            unset($rawDocs[$id]);
        }


        $productIds = array_keys($documents);

        $categoryIds = $this->getCategoryProductIndexData($productIds, $dimension->getValue());

        foreach ($documents as $id => $doc) {
            $doc['category_ids_raw'] = isset($categoryIds[$id]) ? $categoryIds[$id] : [];
            $doc['category_ids_raw'] = array_map(function($item) {return (int) $item;}, $doc['category_ids_raw']);
            $documents[$id]          = $doc;
        }

        return $documents;
    }

    /**
     * @param array $rawData
     * @param int   $id
     * @param array $doc
     *
     * @return mixed
     */
    private function prepareRawData($rawData, $id, $doc)
    {
        $rawData['is_in_stock']  = $this->stockStatusHelper->getProductStockStatus($id);
        $rawData['stock_status'] = $this->stockStatusHelper->getProductStockStatus($id, true); // compatibility with Amasty ShopBy

        foreach ($doc as $key => $value) {
            if (is_array($value) && !in_array($key, ['autocomplete_raw', 'autocomplete'])) {
                $doc[$key] = implode(' ', $value);
                if (isset($rawData[$key]) && $doc[$key] !== implode(' ', $rawData[$key]) && !is_numeric($doc[$key])) {
                    $value = $rawData[$key];
                }

                foreach ($value as $v) {
                    $v = preg_split('/(,|\||\ )/', $v);
                    if (is_array($v)) {
                        foreach ($v as $option) {
                            $doc[$key . '_raw'][] = intval($option);
                        }
                    } else {
                        $doc[$key . '_raw'][] = intval($v);
                    }
                }
                unset($rawData[$key]);
            }
        }

        if (isset($rawData['price']) && isset($rawData['special_price']) && isset($rawData['special_price'][0]) && $rawData['special_price'][0] > 0) {
            if ($rawData['price'][0] > $rawData['special_price'][0]) {
                $rawData['price'] = $rawData['special_price'];
            }
        }

        foreach ($rawData as $attribute => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    if (is_array($item)) {
                        continue;
                    }

                    if (in_array(substr($item, 0, 1), ['{', '['])) {
                        //skip json string
                        $value[$key] = '';
                    }
                }
            } else {
                if (in_array(substr($value, 0, 1), ['{', '['])) {
                    //skip json string
                    continue;
                }
            }

            if (is_scalar($value) || is_array($value)) {
                if ($attribute != 'media_gallery'
                    && $attribute != 'options_container'
                    && $attribute != 'quantity_and_stock_status'
                    && $attribute != 'country_of_manufacture'
                    && $attribute != 'tier_price'
                    && $attribute != 'msrp'
                ) {
                    if (is_array($value) && isset($doc[$attribute]) && in_array($doc[$attribute], $value)) {
                        $doc[$attribute . '_raw'][] = (string) $doc[$attribute];
                    } else {
                        $doc[$attribute . '_raw'] = $value;
                    }
                }
            }
        }

        return $doc;
    }

    /**
     * @param array $productIds
     * @param int   $storeId
     *
     * @return array
     */
    private function getCategoryProductIndexData($productIds, $storeId)
    {
        $productIds[] = 0;

        $connection = $this->resource->getConnection();

        $tableName = $this->resource->getTableName('catalog_category_product_index') . '_store' . $storeId;
        if (!$this->resource->getConnection()->isTableExists($tableName)) {
            $tableName = $this->resource->getTableName('catalog_category_product_index');
            if (strripos($tableName, 'store') !== false) {
                $table     = explode('store', $tableName);
                $table[1]  = $storeId;
                $tableName = implode('store', $table);
            }
        }

        $select = $connection->select()->from(
            [$tableName],
            ['category_id', 'product_id']
        );

        $select->where('product_id IN (?)', $productIds);

        $result = [];
        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][] = $row['category_id'];
        }

        $select = $connection->select()->from(
            [$this->resource->getTableName('catalog_category_product')],
            ['category_id', 'product_id']
        );

        $select->where('product_id IN (?)', $productIds);

        foreach ($connection->fetchAll($select) as $row) {
            $result[$row['product_id']][] = $row['category_id'];
            $result[$row['product_id']]   = array_values(array_unique($result[$row['product_id']]));
        }

        return $result;
    }

    /**
     * @param string $table
     * @param array  $ids
     * @param int    $storeId
     *
     * @return array
     */
    private function eavMap($table, $ids, $storeId)
    {
        $select = $this->resource->getConnection()->select();
        $tableName = $this->resource->getTableName($table);

        if ($this->resource->getConnection()->tableColumnExists($tableName, 'store_id')) {
            $select->from(
                ['eav' => $tableName],
                ['*']
            )->where('eav.store_id in (0, ?)', $storeId);
        } else {
            $select->from(
                ['eav' => $tableName],
                ['*']
            )->where('eav.website_id in (0, ?)', $storeId);
        }

        if (($this->productMetadata->getEdition() == 'Enterprise'
                || $this->productMetadata->getEdition() == 'B2B')
                && !in_array($table, ['catalog_product_index_eav', 'catalog_product_index_price'])) {
            $select->join(
                ['product_entity' => $this->resource->getTableName('catalog_product_entity')],
                'product_entity.row_id = eav.row_id',
                ['parent_id' => 'product_entity.entity_id']
            );
            $select->where('product_entity.entity_id in (?)', $ids);
        } else {
            $select->where('eav.entity_id in (?)', $ids);
        }

        if ($table == 'catalog_product_index_eav' && $this->scopeConfig->getValue('cataloginventory/options/show_out_of_stock') == 0) {
            if ($this->resource->getConnection()->tableColumnExists(
                $this->resource->getTableName('cataloginventory_stock_status'), 'source_id')
            ) {
                $select->join(['stock_status' => $this->resource->getTableName('cataloginventory_stock_status')],
                    'stock_status.product_id = eav.source_id', [])
                    ->where('stock_status.stock_status = 1');
            }
        }

        $result = $this->resource->getConnection()->fetchAll($select);

        $preparedResult = [];
        foreach ($result as $key => $value) {
            if (array_key_exists('attribute_id', $value)) {
                $idKey = array_key_exists('row_id', $value) ? 'row_id': (array_key_exists('entity_id', $value) ? 'entity_id': 'product_id');
                $preparedResult[$key] = $value['attribute_id'] .'|'. $value[$idKey];
            } else {
                continue;
            }
        }

        $duplicates = array_diff_assoc($preparedResult, array_unique($preparedResult));
        $duplicateKeys = array_keys(array_intersect($preparedResult, $duplicates));

        foreach ($duplicateKeys as $key => $duplicateKey) {
            if ($result[$duplicateKey]['store_id'] == 0 && $storeId != 0) {
                unset($result[$duplicateKey]);
            }
        }

        return $result;
    }
}
