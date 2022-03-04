<?php

namespace Amasty\Rma\Controller\Adminhtml;

abstract class AbstractResolution extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Rma::resolution';
}
