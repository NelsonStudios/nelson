<?php

namespace Fecon\RewardPoints\Plugin\Aheadworks\RewardPoints\Model\Service;

use Aheadworks\RewardPoints\Model\Source\Transaction\Type;

class CustomerRewardPointsService
{

    protected $allowedTypes = [
        Type::POINTS_REWARDED_FOR_NEWSLETTER_SIGNUP,
        Type::POINTS_REWARDED_FOR_ORDER,
        Type::POINTS_REWARDED_FOR_REGISTRATION,
        Type::POINTS_REWARDED_FOR_REVIEW_APPROVED_BY_ADMIN,
        Type::POINTS_REWARDED_FOR_SHARES
    ];
    public function beforeSendNotification(
        \Aheadworks\RewardPoints\Model\Service\CustomerRewardPointsService $subject,
        $customerId,
        $notifiedType,
        $data,
        $websiteId = null
    ) {
        if (in_array($notifiedType, $this->allowedTypes)) {
            $this->sendAdminNotification($customerId, $data);
        }
    }

    protected function sendAdminNotification($customerId, $data)
    {
        
    }
}
