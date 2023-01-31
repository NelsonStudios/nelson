<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Logging;

use Amasty\AdminActionsLog\Api\Logging\MetadataInterface;
use Magento\Framework\App\RequestInterface;

class Metadata implements MetadataInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $eventName;

    /**
     * @var object|null
     */
    private $loggingObject;

    public function __construct(
        RequestInterface $request,
        string $eventName,
        object $loggingObject = null
    ) {
        $this->request = $request;
        $this->eventName = $eventName;
        $this->loggingObject = $loggingObject;
    }

    /**
     * @inheritdoc
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritdoc
     */
    public function getObject(): ?object
    {
        return $this->loggingObject;
    }

    /**
     * @inheritdoc
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }
}
