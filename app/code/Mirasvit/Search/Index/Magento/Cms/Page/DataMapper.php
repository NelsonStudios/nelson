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



namespace Mirasvit\Search\Index\Magento\Cms\Page;

use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Service\ContentService;

class DataMapper implements DataMapperInterface
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * DataMapper constructor.
     * @param ContentService $contentService
     */
    public function __construct(
        ContentService $contentService
    ) {
        $this->contentService = $contentService;
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $documents, $dimensions, $index)
    {
        $storeId = current($dimensions)->getValue();

        foreach ($documents as $id => $doc) {
            foreach ($doc as $key => $value) {
                $documents[$id][$key] = $this->contentService->processHtmlContent($storeId, $value);
            }
        }

        return $documents;
    }
}
