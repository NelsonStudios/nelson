<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Marketing\Seo;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class UrlRewrites extends Common
{
    public const CATEGORY = 'admin/url_rewrite/edit';

    /**
     * @var string[]
     */
    protected $dataKeysIgnoreList = [
        '_first_store_id',
        'form_key',
    ];

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite */
        $urlRewrite = $metadata->getObject();

        return [
            LogEntry::ITEM => $urlRewrite->getName(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('URL Rewrite'),
            LogEntry::ELEMENT_ID => (int)$urlRewrite->getId(),
            LogEntry::STORE_ID => (int)$urlRewrite->getStoreId()
        ];
    }
}
