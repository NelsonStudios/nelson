<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Admin Actions Log for Magento 2
 */

namespace Amasty\AdminActionsLog\Model\ActiveSession\ResourceModel;

use Amasty\AdminActionsLog\Model\ActiveSession\ActiveSession as ActiveSessionModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ActiveSession extends AbstractDb
{
    public const TABLE_NAME = 'amasty_audit_active_sessions';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ActiveSessionModel::ID);
    }

    public function updateIfSessionInvalid(int $userId, int $adminSessionInfoId): void
    {
        if (!$this->isSessionValid($userId, $adminSessionInfoId)) {
            $connection = $this->getConnection();
            $connection->update(
                $this->getMainTable(),
                [ActiveSessionModel::ADMIN_SESSION_INFO_ID => $adminSessionInfoId],
                $connection->quoteInto(ActiveSessionModel::USER_ID . ' = ?', $userId)
            );
        }
    }

    private function isSessionValid(int $userId, int $adminSessionInfoId): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            [ActiveSessionModel::ADMIN_SESSION_INFO_ID]
        )->where(
            ActiveSessionModel::USER_ID . ' = ?',
            $userId
        )->limit(1);
        $result = (int)$connection->fetchOne($select);

        return $adminSessionInfoId == $result;
    }
}
