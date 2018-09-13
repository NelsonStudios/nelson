<?php
/**
 * @copyright: Copyright © 2018 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product;
use Magento\ImportExport\Model\Import;

/**
 * Class Downloadable
 */
class Grouped extends \Magento\GroupedImportExport\Model\Import\Product\Type\Grouped
{
    protected $fireLinks;

    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        \Magento\GroupedImportExport\Model\Import\Product\Type\Grouped\Links $links,
        \Firebear\ImportExport\Model\Import\Product\Type\Grouped\Links $fireLinks
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params, $links);

        $this->fireLinks = $fireLinks;
    }

    /**
     * Product entity identifier field
     *
     * @var string
     */
    private $productEntityIdentifierField;

    /**
     * Get product entity identifier field
     *
     * @return string
     */
    private function getProductEntityIdentifierField()
    {
        if (!$this->productEntityIdentifierField) {
            $this->productEntityIdentifierField = $this->getMetadataPool()
                ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
                ->getIdentifierField();
        }
        return $this->productEntityIdentifierField;
    }

    public function saveData()
    {
        $newSku = $this->_entityModel->getNewSku();
        $oldSku = $this->_entityModel->getOldSku();
        $attributes = $this->links->getAttributes();
        $productData = [];
        while ($bunch = $this->_entityModel->getNextBunch()) {
            $linksData = [
                'product_ids' => [],
                'attr_product_ids' => [],
                'position' => [],
                'qty' => [],
                'relation' => []
            ];
            foreach ($bunch as $rowNum => $rowData) {
                if (!isset($rowData[Product::COL_TYPE]) || $this->_type != $rowData[Product::COL_TYPE]) {
                    continue;
                }
                $associatedSkusQty = isset($rowData['associated_skus']) ? $rowData['associated_skus'] : null;
                if (!$this->_entityModel->isRowAllowedToImport($rowData, $rowNum) || empty($associatedSkusQty)) {
                    continue;
                }
                $associatedSkusAndQtyPairs = explode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $associatedSkusQty);
                $position = 0;
                foreach ($associatedSkusAndQtyPairs as $associatedSkuAndQty) {
                    ++$position;
                    $associatedSkuAndQty = explode(self::SKU_QTY_DELIMITER, $associatedSkuAndQty);
                    $associatedSku = isset($associatedSkuAndQty[0]) ? trim($associatedSkuAndQty[0]) : null;
                    if (!isset($newSku[$associatedSku]) && !isset($oldSku[$associatedSku])) {
                        if (strpos($this->_entityModel->getProductMetadata()->getVersion(), '2.2') !== false) {
                            $associatedSku = strtolower($associatedSku);
                        }
                    }
                    if (isset($newSku[$associatedSku])) {
                        $linkedProductId = $newSku[$associatedSku][$this->getProductEntityIdentifierField()];
                    } elseif (isset($oldSku[$associatedSku])) {
                        $linkedProductId = $oldSku[$associatedSku][$this->getProductEntityIdentifierField()];
                    } else {
                        continue;
                    }
                    $scope = $this->_entityModel->getRowScope($rowData);
                    if (Product::SCOPE_DEFAULT == $scope) {
                        if (!isset($newSku[$rowData[Product::COL_SKU]])) {
                            $rowData[Product::COL_SKU] = strtolower($rowData[Product::COL_SKU]);
                        }
                        $productData = $newSku[$rowData[Product::COL_SKU]];
                    } else {
                        $colAttrSet = Product::COL_ATTR_SET;
                        if (!empty($productData)) {
                            $rowData[$colAttrSet] = $productData['attr_set_code'];
                            $rowData[Product::COL_TYPE] = $productData['type_id'];
                        }
                    }
                    if (!empty($productData) && $linkedProductId) {
                        $productId = $productData[$this->getProductEntityLinkField()];
                        $linksData['product_ids'][$productId] = true;
                        $linksData['relation'][] = ['parent_id' => $productId, 'child_id' => $linkedProductId];
                        $qty = empty($associatedSkuAndQty[1]) ? 0 : trim($associatedSkuAndQty[1]);
                        $linksData['attr_product_ids'][$productId] = true;
                        $linksData['position']["{$productId} {$linkedProductId}"] = [
                            'product_link_attribute_id' => $attributes['position']['id'],
                            'value' => $position
                        ];
                        if ($qty) {
                            $linksData['attr_product_ids'][$productId] = true;
                            $linksData['qty']["{$productId} {$linkedProductId}"] = [
                                'product_link_attribute_id' => $attributes['qty']['id'],
                                'value' => $qty
                            ];
                        }
                    }
                }
            }
            $this->fireLinks->saveLinksData($linksData);
        }

        return $this;
    }
}
