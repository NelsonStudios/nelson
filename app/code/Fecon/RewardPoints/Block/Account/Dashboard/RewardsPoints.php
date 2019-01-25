<?php

namespace Fecon\RewardPoints\Block\Account\Dashboard;

use Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface;

/**
 * Block to display Customer's Rewards Points
 */
class RewardsPoints extends \Magento\Customer\Block\Account\Dashboard\Info
{

    /**
     * @var CustomerRewardPointsManagementInterface
     */
    protected $customerRewardPointsService;

    /**
     * @var \Fecon\RewardPoints\Helper\ConfigHelper 
     */
    protected $configHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param CustomerRewardPointsManagementInterface $customerRewardPointsService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
        CustomerRewardPointsManagementInterface $customerRewardPointsService,
        \Fecon\RewardPoints\Helper\ConfigHelper $configHelper,
        array $data = array()
    ) {
        $this->customerRewardPointsService = $customerRewardPointsService;
        $this->configHelper = $configHelper;
        parent::__construct($context, $currentCustomer, $subscriberFactory, $helperView, $data);
    }

    /**
     * Get customer balance in points
     *
     * @return int
     */
    public function getCustomerRewardPointsBalance()
    {
        return (int)$this->customerRewardPointsService->getCustomerRewardPointsBalance(
            $this->currentCustomer->getCustomerId()
        );
    }

    public function getLevelImage($rewardsPoints)
    {
        $image = $this->getViewFileUrl('Fecon_RewardPoints::images/bronze-medal.png');
        $minimumSilverAmount = $this->configHelper->getMinimumSilverLevel();
        $minimumGoldAmount = $this->configHelper->getMinimumGoldLevel();
        if ($rewardsPoints > $minimumGoldAmount) {
            $image = $this->getViewFileUrl('Fecon_RewardPoints::images/gold-medal.png');
        } elseif ($rewardsPoints > $minimumSilverAmount) {
            $image = $this->getViewFileUrl('Fecon_RewardPoints::images/silver-medal.png');
        }

        return $image;
    }

    public function getLevel($rewardsPoints)
    {
        $level = 'Bronze';
        $minimumSilverAmount = $this->configHelper->getMinimumSilverLevel();
        $minimumGoldAmount = $this->configHelper->getMinimumGoldLevel();
        if ($rewardsPoints > $minimumGoldAmount) {
            $level = 'Gold';
        } elseif ($rewardsPoints > $minimumSilverAmount) {
            $level = 'Silver';
        }

        return $level;
    }
}