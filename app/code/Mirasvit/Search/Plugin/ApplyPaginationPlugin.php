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

use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Mirasvit\Core\Service\CompatibilityService;

/**
 * @see \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::_loadEntities
 * Apply limits for collection. In the Magento 2.3.4 limits applied only for native mysql engine.
 */
class ApplyPaginationPlugin
{
    /**
     * @param Collection $subject
     * @param bool       $printQuery
     * @param bool       $logQuery
     *
     * @return array
     */
    public function before_loadEntities($subject, $printQuery = false, $logQuery = false)
    {
        if (!CompatibilityService::is23()) {
            return [$printQuery, $logQuery];
        }

        if ($subject->getPageSize()) {
            $subject->getSelect()->limitPage($subject->getCurPage(), $subject->getPageSize());
        }

        return [$printQuery, $logQuery];
    }
}
