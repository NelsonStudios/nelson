<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search
 * @version   1.0.156
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Model\ResourceModel\Fulltext\Collection\Parents;

use Magento\Framework\ObjectManagerInterface;

class SearchCriteriaResolverParent
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var null
     */
    private $resolver;

    /**
     * SearchCriteriaResolverParent constructor.
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        if (class_exists('\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolver')) {
            $this->resolver = $this->objectManager->create(
                '\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchCriteriaResolver'
            );
        } else {
            $this->resolver = null;
        }
    }

    /**
     * @return mixed
     */
    public function resolve()
    {
        if ($this->resolver) {
            return $this->resolver->resolve();
        }
    }
}
