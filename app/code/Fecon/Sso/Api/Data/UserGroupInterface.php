<?php


namespace Fecon\Sso\Api\Data;

interface UserGroupInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const USERGROUP_ID = 'usergroup_id';
    const NAME = 'name';

    /**
     * Get usergroup_id
     * @return string|null
     */
    public function getUsergroupId();

    /**
     * Set usergroup_id
     * @param string $usergroupId
     * @return \Fecon\Sso\Api\Data\UserGroupInterface
     */
    public function setUsergroupId($usergroupId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Fecon\Sso\Api\Data\UserGroupInterface
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Fecon\Sso\Api\Data\UserGroupExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Fecon\Sso\Api\Data\UserGroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Fecon\Sso\Api\Data\UserGroupExtensionInterface $extensionAttributes
    );
}
