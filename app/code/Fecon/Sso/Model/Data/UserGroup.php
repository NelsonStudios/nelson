<?php


namespace Fecon\Sso\Model\Data;

use Fecon\Sso\Api\Data\UserGroupInterface;

class UserGroup extends \Magento\Framework\Api\AbstractExtensibleObject implements UserGroupInterface
{

    /**
     * Get usergroup_id
     * @return string|null
     */
    public function getUsergroupId()
    {
        return $this->_get(self::USERGROUP_ID);
    }

    /**
     * Set usergroup_id
     * @param string $usergroupId
     * @return \Fecon\Sso\Api\Data\UserGroupInterface
     */
    public function setUsergroupId($usergroupId)
    {
        return $this->setData(self::USERGROUP_ID, $usergroupId);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Fecon\Sso\Api\Data\UserGroupInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Fecon\Sso\Api\Data\UserGroupExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Fecon\Sso\Api\Data\UserGroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Fecon\Sso\Api\Data\UserGroupExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
