<?php

namespace Fecon\OrsProducts\Model\Handler;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of ImageUpdater
 */
class ImageUpdater extends BaseHandler
{

    protected $filesystem;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Website\Link $productWebsiteLink,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Fecon\OrsProducts\Helper\CategoryHelper $categoryHelper,
        \Magento\Catalog\Model\ResourceModel\Product $productResource, 
        \Fecon\OrsProducts\Helper\AttributeHelper $attributeHelper,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        parent::__construct($productRepository, $productFactory, $productWebsiteLink, $stockRegistry, $categoryLinkManagement, $categoryHelper, $productResource, $attributeHelper);
    }

    public function configure()
    {
        if (empty($this->configuration)) {
            $this->configureAttributePositions();
        }
    }

    public function configureAttributePositions()
    {
        if (empty($this->configuration)) {
            $this->configuration = [
                'sku' => [
                    'position' => 0,
                    'type' => self::TYPE_STRING
                ],
                'base_image' => [
                    'position' => 1,
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
        $success = true;
        try {
            $baseImage = $product->getData('image');
            if (!$baseImage) {
                $newBaseImage = $this->getAttributeValue('base_image', $rawData);
                $absolutePath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
                $product->setData('image', $absolutePath. "import/" . $newBaseImage);
                $this->productRepository->save($product);
                $message = 'Product ' . $product->getName() . ' updated';
            } else {
                $message = '';
            }
        } catch (\Exception $ex) {
            $success = false;
            $message = $ex->getMessage();
        }

        return $success;
    }
}