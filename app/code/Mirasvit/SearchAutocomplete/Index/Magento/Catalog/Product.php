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
 * @package   mirasvit/module-search-autocomplete
 * @version   1.2.10
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchAutocomplete\Index\Magento\Catalog;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Helper\Stock as StockHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryFactory;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;
use Mirasvit\SearchAutocomplete\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Product extends AbstractIndex
{

    private $productMapper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StockHelper
     */
    private $stockHelper;

    /**
     * @var SummaryFactory
     */
    private $summaryFactory;

    /**
     * @var array
     */
    private $reviews = [];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Product\Mapper $productMapper,
        Config $config,
        RequestInterface $request,
        StockHelper $stockHelper,
        SummaryFactory $summaryFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->productMapper         = $productMapper;
        $this->config                = $config;
        $this->request               = $request;
        $this->stockHelper           = $stockHelper;
        $this->summaryFactory        = $summaryFactory;
        $this->collectionFactory     = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items      = [];
        $categoryId = intval($this->request->getParam('cat'));
        $storeId    = intval($this->request->getParam('store_id'));

        $collection = $this->getCollection();

        $collection->addAttributeToSelect('name')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('description');

        try {
            $collection->getSelect()->order('score DESC');
        } catch (\Exception $e) {
            $collection->load();
            if (array_key_exists('search_result', $collection->getSelect()->getPart('from'))) {
                $collection->getSelect()->order('score DESC');
            }
        }

        if (!$this->config->isOutOfStockAllowed()) {
            try {
                $this->stockHelper->addInStockFilterToCollection($collection);
            } catch (\Exception $e) {
            }
        }

        if ($categoryId) {
            $om       = ObjectManager::getInstance();
            $category = $om->create(\Magento\Catalog\Model\Category::class)->load($categoryId);
            $collection->addCategoryFilter($category);
        }

        if ($this->config->isShowRating()) {
            $this->prepareRatingData($collection->getAllIds(), $storeId);
        }

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $map = $this->mapProduct($product, $storeId);

            if ($map) {
                $items[] = $map;
            }
        }

        return $items;
    }

    /**
     * @param array                                         $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     *
     * @return array
     */
    public function map($documents, $dimensions)
    {
        if (!$this->config->isFastMode() || count($documents) === 0) {
            return $documents;
        }

        $storeId    = current($dimensions)->getValue();
        $productIds = array_keys($documents);

        $productCollection = $this->collectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addStoreFilter($storeId);


        if ($this->config->isShowRating()) {
            $this->prepareRatingData($productIds, $storeId);
        }

        foreach ($productCollection as $product) {
            $documents[$product->getId()]['autocomplete'] = $this->mapProduct($product, $storeId);
        }

        unset($productCollection);

        return $documents;
    }

    /**
     * @param array $productIds
     * @param int   $storeId
     */
    private function prepareRatingData($productIds, $storeId)
    {
        $reviewsCollection = $this->summaryFactory->create()
            ->addEntityFilter($productIds)
            ->addStoreFilter($storeId);

        $reviewsCollection->getSelect()->group('entity_pk_value');
        $reviewsCollection->load();

        /** @var \Magento\Review\Model\Review\Summary $reviewSummary */
        foreach ($reviewsCollection as $reviewSummary) {
            $this->reviews[$reviewSummary->getData('entity_pk_value')] = $reviewSummary;
        }
    }

    /**
     * @param ProductInterface|\Magento\Catalog\Model\Product $product
     * @param int                                             $storeId
     *
     * @return array
     */
    private function mapProduct(ProductInterface $product, $storeId = 1)
    {
        return [
            'name'        => $this->productMapper->getProductName($product),
            'url'         => $this->productMapper->getProductUrl($product, $storeId),
            'sku'         => $this->productMapper->getProductSku($product),
            'description' => $this->productMapper->getDescription($product),
            'image'       => $this->productMapper->getProductImage($product, $storeId),
            'price'       => $this->productMapper->getPrice($product, $storeId),
            'rating'      => $this->productMapper->getRating($product, $storeId, $this->reviews),
            'cart'        => $this->productMapper->getCart($product, $storeId),
            'stock_status'=> $this->productMapper->getStockStatus($product, $storeId),
            'optimize'    => false,
        ];
    }
}
