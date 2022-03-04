<?php

namespace Amasty\Rma\Controller\Adminhtml\ReturnRules;

use Amasty\Rma\Controller\Adminhtml\AbstractReturnRules;

class Create extends AbstractReturnRules
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
