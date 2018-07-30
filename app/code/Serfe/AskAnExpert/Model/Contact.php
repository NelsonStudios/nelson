<?php
namespace Serfe\AskAnExpert\Model;

class Contact extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Aion Test cache tag
     */
    const CACHE_TAG = 'askanexpert_contact';

    /**
     * @var string
     */
    protected $_cacheTag = 'askanexpert_contact';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'askanexpert_contact';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Serfe\AskAnExpert\Model\ResourceModel\Contact');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Prepare item's statuses
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
