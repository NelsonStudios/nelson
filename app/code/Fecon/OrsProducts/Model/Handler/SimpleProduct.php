<?php

namespace Fecon\OrsProducts\Model\Handler;

/**
 * Handler for simple products
 */
class SimpleProduct extends BaseHandler
{

    public function configureAttributePositions()
    {
        if (empty($this->configuration)) {
            $this->configuration = [
                'sku' => [
                    'position' => 0,
                    'type' => self::TYPE_STRING
                ],
                '_attribute_set' => [
                    'position' => 1,
                    'type' => self::TYPE_STRING
                ],
                'product_type' => [
                    'position' => 2,
                    'type' => self::TYPE_STRING
                ],
                'unspsc' => [
                    'position' => 3,
                    'type' => self::TYPE_STRING
                ],
                'upc' => [
                    'position' => 4,
                    'type' => self::TYPE_STRING
                ],
                'mfg_part_number' => [
                    'position' => 5,
                    'type' => self::TYPE_STRING
                ],
                'web_uom' => [
                    'position' => 6,
                    'type' => self::TYPE_SELECT
                ],
                'categories' => [
                    'position' => 7,
                    'type' => self::TYPE_STRING
                ],
                'family' => [
                    'position' => 8,
                    'type' => self::TYPE_SELECT
                ],
                'manufacturer' => [
                    'position' => 9,
                    'type' => self::TYPE_SELECT
                ],
                'manufacturer_url' => [
                    'position' => 10,
                    'type' => self::TYPE_STRING
                ],
                'manufacturer_logo' => [
                    'position' => 11,
                    'type' => self::TYPE_STRING
                ],
                'name' => [
                    'position' => 12,
                    'type' => self::TYPE_HTML
                ],
                'hazmat' => [
                    'position' => 13,
                    'type' => self::TYPE_SELECT
                ],
                'short_description' => [
                    'position' => 14,
                    'type' => self::TYPE_HTML
                ],
                'testing_and_approvals' => [
                    'position' => 15,
                    'type' => self::TYPE_STRING
                ],
                'minimum_order' => [
                    'position' => 16,
                    'type' => self::TYPE_SELECT
                ],
                'standard_pack' => [
                    'position' => 17,
                    'type' => self::TYPE_SELECT
                ],
                'prop_65_warning_required' => [
                    'position' => 18,
                    'type' => self::TYPE_STRING
                ],
                'prop_65_warning_label' => [
                    'position' => 19,
                    'type' => self::TYPE_STRING
                ],
                'prop_65_warning_message' => [
                    'position' => 20,
                    'type' => self::TYPE_STRING
                ],
                'attributes' => [
                    'position' => 21,
                    'type' => self::TYPE_HTML
                ],
                'part_number' => [
                    'position' => 0,
                    'type' => self::TYPE_STRING
                ],
                'meta_title' => [
                    'position' => 12,
                    'type' => self::TYPE_HTML
                ],
                'meta_description' => [
                    'position' => 14,
                    'type' => self::TYPE_HTML
                ],
                'minimum_order_raw' => [
                    'position' => 16,
                    'type' => self::TYPE_STRING
                ],
                'web_uom_raw' => [
                    'position' => 6,
                    'type' => self::TYPE_STRING
                ]
            ];
        }
    }

    public function configureCustomAttributes()
    {
        $this->customAttributes = [
            'unspsc',
            'upc',
            'mfg_part_number',
            'short_description',
            'attributes',
            'part_number',
            'manufacturer',
            'web_uom',
            'minimum_order',
            'standard_pack',
            'meta_title',
            'meta_description',
            'family'
        ];
    }

    public function configureAttributesToUpdate()
    {
        $this->attributesToUpdate = [
            'unspsc',
            'upc',
            'mfg_part_number',
            'short_description',
            'attributes',
            'part_number',
            'manufacturer',
            'web_uom',
            'minimum_order',
            'standard_pack',
            'meta_title',
            'meta_description',
            'family'
        ];
    }

    public function configure()
    {
        if (empty($this->configuration)) {
            $this->configureAttributePositions();
        }
        if (empty($this->customAttributes)) {
            $this->configureCustomAttributes();
        }
        if (empty($this->attributesToUpdate)) {
            $this->configureAttributesToUpdate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processData($row, &$message = '')
    {
        $this->configure();
        $sku = $this->getAttributeValue('sku', $row);
        $found = true;
        try {
            $product = $this->productRepository->get($sku);
            $success = $this->updateProduct($product, $row, $message);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $message = $e->getMessage();
            $found = false;
        }
        if (!$found) {
            if (strpos($sku, 'EC-') !== 0) {
                try {
                    $sku = 'EC-' . $sku;
                    $product = $this->productRepository->get($sku);
                    $success = $this->updateProduct($product, $row, $message);
                    $found = true;
                } catch (\Magento\Framework\Exception\NoSuchEntityException $ex) { }
            }
            if (!$found) {
                $success = $this->createProduct($row, $message);
            }
        }

        return $success;
    }

    public function getAttributeSetId()
    {
        return 13;  // ORS Products
    }

    /**
     * {@inheritdoc}
     */
    public function updateProduct($product, $rawData, &$message = '')
    {
        $success = parent::updateProduct($product, $rawData, $message);
        $specialFieldsSuccess = $this->updateSpecialFields($product, $rawData, $message);

        return $success && $specialFieldsSuccess;
    }

    /**
     * Update $product special fields
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $rawData
     * @param string $message
     * @return boolean
     */
    protected function updateSpecialFields($product, $rawData, &$message = '')
    {
        $success = true;
        try {
            $product->setData('exists_in_syteline', 1);
            $this->productResource->saveAttribute($product, 'exists_in_syteline');
            $name = $this->getAttributeValue('name', $rawData);
            $minimumOrder = $this->getAttributeValue('minimum_order_raw', $rawData);
            $webUom = $this->getAttributeValue('web_uom_raw', $rawData);
            if ($minimumOrder && $webUom) {
                $newName = $name . " - [ " . $minimumOrder . " / " . $webUom . " ]";
                $product->setData('name', $newName);
                $this->productResource->saveAttribute($product, 'name');
            }
            $sku = $product->getSku();
            if (strpos($sku, 'EC-') !== 0) {
                $newSku = 'EC-' . $sku;
                $product->setData('sku', $newSku);
                $this->productRepository->save($product);
            }
            $message = 'Product ' . $product->getName() . ' updated';
        } catch (\Exception $ex) {
            $success = false;
            $message = $ex->getMessage();
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    protected function updateProductStock($product, $rawData)
    {
        $manageStock = 0;
        $qty = 0;
        $isInStock = 1;
        $stockItem = $this->stockRegistry->getStockItemBySku($product->getSku());
        $stockItem->setManageStock($manageStock);
        $stockItem->setUseConfigManageStock(0);
        $stockItem->setIsInStock($isInStock);
        $stockItem->setQty($qty);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
    }
}