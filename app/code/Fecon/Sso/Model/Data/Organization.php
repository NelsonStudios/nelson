<?php


namespace Fecon\Sso\Model\Data;

use Fecon\Sso\Api\Data\OrganizationInterface;

class Organization extends \Magento\Framework\Api\AbstractExtensibleObject implements OrganizationInterface
{

    /**
     * Get organization_id
     * @return string|null
     */
    public function getOrganizationId()
    {
        return $this->_get(self::ORGANIZATION_ID);
    }

    /**
     * Set organization_id
     * @param string $organizationId
     * @return \Fecon\Sso\Api\Data\OrganizationInterface
     */
    public function setOrganizationId($organizationId)
    {
        return $this->setData(self::ORGANIZATION_ID, $organizationId);
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
     * @return \Fecon\Sso\Api\Data\OrganizationInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Fecon\Sso\Api\Data\OrganizationExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Fecon\Sso\Api\Data\OrganizationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Fecon\Sso\Api\Data\OrganizationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
