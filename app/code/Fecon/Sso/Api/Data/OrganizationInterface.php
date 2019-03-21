<?php


namespace Fecon\Sso\Api\Data;

interface OrganizationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const ORGANIZATION_ID = 'organization_id';
    const NAME = 'name';

    /**
     * Get organization_id
     * @return string|null
     */
    public function getOrganizationId();

    /**
     * Set organization_id
     * @param string $organizationId
     * @return \Fecon\Sso\Api\Data\OrganizationInterface
     */
    public function setOrganizationId($organizationId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Fecon\Sso\Api\Data\OrganizationInterface
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Fecon\Sso\Api\Data\OrganizationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Fecon\Sso\Api\Data\OrganizationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Fecon\Sso\Api\Data\OrganizationExtensionInterface $extensionAttributes
    );
}
