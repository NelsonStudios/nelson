<?php

namespace Fecon\OrsProducts\Model\Handler;

/**
 * Price Handler
 */
class PriceHandler extends BaseHandler
{

    public function configureAttributePositions()
    {
        if (empty($this->configuration)) {
            $this->configuration = [
                'sku' => [
                    'position' => 0,
                    'type' => self::TYPE_STRING
                ],
                'price' => [
                    'position' => 16,
                    'type' => self::TYPE_STRING
                ],
                'weight' => [
                    'position' => 31,
                    'type' => self::TYPE_STRING
                ],
                'ts_dimensions_height' => [
                    'position' => 32,
                    'type' => self::TYPE_STRING
                ],
                'ts_dimensions_width' => [
                    'position' => 33,
                    'type' => self::TYPE_STRING
                ],
                'ts_dimensions_length' => [
                    'position' => 34,
                    'type' => self::TYPE_STRING
                ]
            ];
        }
    }

    public function configure()
    {
        if (empty($this->configuration)) {
            $this->configureAttributePositions();
        }
        if (empty($this->attributesToUpdate)) {
            $this->configureAttributesToUpdate();
        }
    }

    public function configureAttributesToUpdate()
    {
        $this->attributesToUpdate = [
            'weight',
            'ts_dimensions_height',
            'ts_dimensions_width',
            'ts_dimensions_length'
        ];
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
        } catch (\Exception $e) {
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
                } catch (\Exception $ex) { }
            }
            if (!$found) {
                $success = false;
            }
        }

        return $success;
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
            $value = floatval($this->getAttributeValue('price', $rawData)) * 1.2;
            $product->setData('price', $value);
            $this->productResource->saveAttribute($product, 'price');
            $message = 'Product ' . $product->getName() . ' updated';
        } catch (\Exception $ex) {
            $success = false;
            $message = $ex->getMessage();
        }

        return $success;
    }
}