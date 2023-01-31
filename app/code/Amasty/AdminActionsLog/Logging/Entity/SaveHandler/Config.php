<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging\Entity\SaveHandler;

use Amasty\AdminActionsLog\Api\Logging\EntitySaveHandlerInterface;
use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Amasty\AdminActionsLog\Model\LogEntry\LogEntry;
use Amasty\AdminActionsLog\Model\OptionSource\LogEntryTypes;
use Amasty\Base\Model\Serializer;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config implements EntitySaveHandlerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Serializer $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    public function getLogMetadata(MetadataInterface $metadata): array
    {
        /** @var \Magento\Framework\App\Config\Value $object */
        $object = $metadata->getObject();

        return [
            LogEntry::TYPE => LogEntryTypes::TYPE_EDIT,
            LogEntry::CATEGORY_NAME => __('System Config'),
            LogEntry::ELEMENT_ID => (int)$object->getId(),
            LogEntry::STORE_ID => (int)$object->getScopeId()
        ];
    }

    /**
     * @param \Magento\Framework\App\Config\Value $object
     * @return array
     */
    public function processBeforeSave($object): array
    {
        $oldValue = $this->scopeConfig->getValue($object->getPath());

        if (is_array($oldValue)) {
            $oldValue = $this->serializer->serialize($oldValue);
        }

        return [
            $object->getPath() => $oldValue
        ];
    }

    /**
     * @param \Magento\Framework\App\Config\Value $object
     * @return array
     */
    public function processAfterSave($object): array
    {
        return [
            $object->getPath() => $object->getValue()
        ];
    }
}
