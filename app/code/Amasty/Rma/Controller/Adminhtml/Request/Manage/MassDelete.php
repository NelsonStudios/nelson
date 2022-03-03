<?php
declare(strict_types=1);

namespace Amasty\Rma\Controller\Adminhtml\Request\Manage;

use Amasty\Rma\Controller\Adminhtml\Request\AbstractMassDelete;

class MassDelete extends AbstractMassDelete
{
    public const ADMIN_RESOURCE = 'Amasty_Rma::manage_delete';
}
