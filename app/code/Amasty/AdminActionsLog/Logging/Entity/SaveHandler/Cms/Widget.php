<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Cms;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Logging\Entity\SaveHandler\Common;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;

class Widget extends Common
{
    public const CATEGORY = 'admin/widget_instance/edit/';

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Widget\Model\Widget\Instance $widget */
        $widget = $metadata->getObject();

        return [
            LogEntry::ITEM => $widget->getTitle(),
            LogEntry::CATEGORY => self::CATEGORY,
            LogEntry::CATEGORY_NAME => __('CMS Widget'),
            LogEntry::ELEMENT_ID => (int)$widget->getId(),
            LogEntry::STORE_ID => (int)$widget->getStoreId(),
            LogEntry::PARAMETER_NAME => 'instance_id'
        ];
    }
}
