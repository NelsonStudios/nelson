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
 * @package   mirasvit/module-search
 * @version   1.0.156
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Plugin;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;

/**
 * @see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::prepareProductIndex()
 */
class SearchIndexerPlugin
{
    /**
     * Mirasvit\Search\Api\Repository\IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * SearchIndexerPlugin constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    /**
     * @param DataProvider $dataProvider
     * @param array        $attributeData
     * @param array|null   $productData
     * @param array|null   $productAdditional
     * @param int|null     $storeId
     *
     * @return mixed
     */
    public function afterPrepareProductIndex(
        $dataProvider,
        $attributeData,
        $productData = null,
        $productAdditional = null,
        $storeId = null
    ) {
        if ($productData === null || count($productData) === 0) {
            return $attributeData;
        }

        $includeBundled = $this->getIndex()->getProperty('include_bundled');
        $productData    = array_values($productData)[0];
        if (!$includeBundled && !empty($this->getIndex()->getAttributes())) {
            foreach ($attributeData as $attributeId => $value) {
                $attribute = $dataProvider->getSearchableAttribute($attributeId);
                if (!array_key_exists($attribute->getAttributeCode(), $this->getIndex()->getAttributes())) {
                    unset($attributeData[$attributeId]);
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $key => $option) {
                        $value[$key] = preg_replace('/(\d.*\|)/', '', $option);
                    }
                } else {
                    $value = preg_replace('/(attr.*\|)/', '', $value);
                }

                if (!empty($value) && in_array($attribute->getFrontendInput(), ['multiselect', 'select'])) {
                    $attributeData[$attributeId] = $value;
                    continue;
                }

                if (isset($productData[$attributeId])) {
                    $attributeData[$attributeId] = trim(strip_tags($productData[$attributeId]));
                }
            }
        }

        return $attributeData;
    }

    /**
     * @return \Mirasvit\Search\Api\Data\IndexInterface
     */
    private function getIndex()
    {
        return $this->indexRepository->get('catalogsearch_fulltext');
    }
}
