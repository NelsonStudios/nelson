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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchAutocomplete\Index\AbstractIndex;

class Category extends AbstractIndex
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var Url
     */
    private $urlBuilder;

    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Url $urlBuilder
    ) {
        $this->storeManager       = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->urlBuilder         = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        $items = [];

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($this->getCollection() as $category) {
            $items[] = $this->mapCategory($category, $this->storeManager->getStore()->getId());
        }

        return $items;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int                             $storeId
     *
     * @return array
     */
    public function mapCategory($category, $storeId)
    {
        $category = $category->setStoreId($storeId);
        $category = $category->load($category->getId());

        return [
            'name' => $this->getFullPath($category, $storeId),
            'url'  => $this->getCategoryUrl($category, $storeId),
        ];
    }

    /**
     * List of parent categories
     *
     * @param CategoryInterface $category
     * @param int               $storeId
     *
     * @return string
     */
    public function getFullPath(CategoryInterface $category, $storeId)
    {
        $store  = $this->storeManager->getStore($storeId);
        $rootId = $store->getRootCategoryId();

        $result = [
            $category->getName(),
        ];

        do {
            if (!$category->getParentId()) {
                break;
            }
            $category = $this->categoryRepository->get($category->getParentId());
            $category = $category->setStoreId($storeId);
            $category->load($category->getId());

            if (!$category->getIsActive() && $category->getId() != $rootId) {
                break;
            }

            if ($category->getId() != $rootId) {
                $result[] = $category->getName();
            }
        } while ($category->getId() != $rootId);

        $result = array_reverse($result);

        return implode('<i>›</i>', $result);
    }

    /**
     * @param array $data
     * @param array $dimensions
     *
     * @return array
     */
    public function map($data, $dimensions)
    {
        $dimension = current($dimensions);
        $storeId   = $dimension->getValue();

        foreach ($data as $entityId => $itm) {
            $entity = ObjectManager::getInstance()->create(\Magento\Catalog\Model\Category::class)
                ->load($entityId);

            $map = $this->mapCategory($entity, $storeId);

            $data[$entityId]['autocomplete'] = $map;
        }

        return $data;
    }

    /**
     * @param CategoryInterface $category
     * @param int               $storeId
     *
     * @return string
     */
    private function getCategoryUrl($category, $storeId)
    {
        $emulation = ObjectManager::getInstance()->get(\Magento\Store\Model\App\Emulation::class);

        try {
            $emulation->startEnvironmentEmulation($storeId, 'frontend', true);
            $url = $category->getUrl();
            $baseUrl = $this->storeManager->getStore($storeId)->getBaseUrl();

            if (strripos($url, $baseUrl) === false || strripos($url, $this->getAdminPath()) !== false) {
                $url = rtrim($baseUrl, '/') . parse_url($url)['path'];
            }

        } catch (\Exception $e) {
            $state = ObjectManager::getInstance()->get(\Magento\Framework\App\State::class);
            $state->emulateAreaCode(
                'frontend',
                function (&$url, $baseUrl, $category, $storeId) {
                    if (strripos($url, $baseUrl) === false || strripos($url, $this->getAdminPath()) !== false) {
                        $url = rtrim($baseUrl, '/') . parse_url($url)['path'];
                    }
                },
                [&$url, $baseUrl, $category, $storeId]
            );
        } finally {
            $emulation->stopEnvironmentEmulation();
        }

        return $url;

    }

    /**
     * @return string (mageadmin)
     */
    private function getAdminPath()
    {
        $url = ObjectManager::getInstance()->get(\Magento\Backend\Helper\Data::class)
            ->getHomePageUrl();

        $components = parse_url($url);

        return explode('/', $components['path'])[1];
    }
}
