<?php

namespace Amasty\Rma\Controller\Adminhtml\Reason;

use Amasty\Rma\Controller\Adminhtml\AbstractReason;

class Create extends AbstractReason
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
