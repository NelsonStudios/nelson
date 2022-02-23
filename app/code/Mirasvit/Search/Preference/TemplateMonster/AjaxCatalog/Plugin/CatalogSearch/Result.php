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



namespace Mirasvit\Search\Preference\TemplateMonster\AjaxCatalog\Plugin\CatalogSearch;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\ObjectManagerInterface;

class Result
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * Result constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Resolver $layerResolver
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Resolver $layerResolver
    ) {
        $this->objectManager = $objectManager;
        $this->layerResolver = $layerResolver;
    }

    /**
     * @param mixed $subject
     * @param \Closure $proceed
     * @return mixed
     */
    public function aroundExecute($subject, \Closure $proceed)
    {
        $helper = $this->objectManager
            ->get('TemplateMonster\AjaxCatalog\Helper\Catalog\View\ContentAjaxResponse');

        $request = $subject->getRequest();
        if ($request->isXmlHttpRequest()) {
            return $helper->getAjaxSearchResult($subject, $proceed);
        } else {
            $returnValue = $proceed();

            return $returnValue;
        }
    }
}
