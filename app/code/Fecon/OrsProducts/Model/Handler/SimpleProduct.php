<?php

namespace Fecon\OrsProducts\Model\Handler;

/**
 * Handler for simple products
 */
class SimpleProduct extends BaseHandler
{

    protected $configuration = [];

    protected $productRepository;

    protected $productFactory;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productFactory = $productFactory;
    }

    public function configure()
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
                    'type' => self::TYPE_STRING
                ],
                'hazmat' => [
                    'position' => 13,
                    'type' => self::TYPE_STRING
                ],
                'description' => [
                    'position' => 14,
                    'type' => self::TYPE_STRING
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
                    'type' => self::TYPE_STRING
                ]
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processData($row, &$message = '')
    {
        $sku = $this->getProductSku($row);
        try {
            $product = $this->productRepository->get($sku);
            $isNew = false;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $product = $this->productFactory->create();
            $isNew = true;
        }
        return true;
    }

    protected function getProductSku($data)
    {
        $position = $this->configuration['sku']['position'];

        return $data[$position];
    }
}