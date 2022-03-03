<?php

namespace Amasty\Rma\Controller\Adminhtml\Condition;

use Amasty\Rma\Controller\Adminhtml\AbstractCondition;

class Create extends AbstractCondition
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
