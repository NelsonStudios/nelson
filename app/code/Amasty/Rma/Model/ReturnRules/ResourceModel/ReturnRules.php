<?php

namespace Amasty\Rma\Model\ReturnRules\ResourceModel;

use Amasty\Rma\Api\Data\ReturnRulesInterface;
use Magento\Rule\Model\ResourceModel\AbstractResource;

class ReturnRules extends AbstractResource
{
    public const TABLE_NAME = 'amasty_rma_return_rules';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ReturnRulesInterface::ID);
    }
}
