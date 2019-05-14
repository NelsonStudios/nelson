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
                    'type' => self::TYPE_STRING
                ],
                'categories' => [
                    'position' => 7,
                    'type' => self::TYPE_STRING
                ],
                'family' => [
                    'position' => 8,
                    'type' => self::TYPE_STRING
                ],
                'manufacturer' => [
                    'position' => 9,
                    'type' => self::TYPE_STRING
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
                    'type' => self::TYPE_STRING
                ],
                'description' => [
                    'position' => 14,
                    'type' => self::TYPE_HTML
                ],
                'testing_and_approvals' => [
                    'position' => 15,
                    'type' => self::TYPE_STRING
                ],
                'minimum_order' => [
                    'position' => 16,
                    'type' => self::TYPE_STRING
                ],
                'standard_pack' => [
                    'position' => 17,
                    'type' => self::TYPE_STRING
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
                ]
            ];
        }
    }

    public function configureCustomAttributes()
    {
        $this->customAttributes = [];
    }

    public function configureAttributesToUpdate()
    {
        $this->attributesToUpdate = [
            'unspsc',
            'upc',
            'mfg_part_number',
            'description',
            'attributes'
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
        try {
            $product = $this->productRepository->get($sku);
            $success = $this->updateProduct($product, $row, $message);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
//            $success = $this->createProduct($row, $message);
            $message = $e->getMessage();
            $success = false;
        }

        return $success;
    }

    public function getAttributeSetId()
    {
        return 13;  // ORS Products
    }
}