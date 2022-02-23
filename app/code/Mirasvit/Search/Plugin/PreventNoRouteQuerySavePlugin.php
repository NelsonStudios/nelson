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



namespace Mirasvit\Search\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Search\Model\ResourceModel\Query;
use Magento\Search\Model\Query as QueryModel;

/**
 * @see \Magento\Search\Model\ResourceModel\Query::saveNumResults()
 */
class PreventNoRouteQuerySavePlugin
{
    /**
     * @var RequestInterface 
     */
    private $request;

    /**
     * NoRoutePlugin constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Save query with number of results
     *
     * @param Query $subject
     * @param callable $proceed
     * @param QueryModel $query
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function  aroundSaveNumResults(Query $subject, callable $proceed, QueryModel $query)
    {
        if (!$this->request->getParam('404')) {
            $proceed($query);
        }        
    }
}
