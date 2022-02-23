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
 * @package   mirasvit/module-search-elastic
 * @version   1.2.75
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SearchElastic\Plugin\Magento\CatalogSearch\Model\Indexer\Scope;

use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Search\Model\Config;

class IndexSwitcherPlugin
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Scope\State
     */
    private $state;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Mirasvit\SearchElastic\Model\Engine
     */
    private $engine;

    /**
     * @var mixed
     */
    private $resolver;
    
    /**
     * @var Config
     */
    private $config;

    /**
     * IndexSwitcherPlugin constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\SearchElastic\Model\Engine $engine
     * @param Config $config
     */
    public function __construct(
        // \Mirasvit\SearchElastic\Model\Indexer\Scope\StateMediator $state,
        \Magento\Framework\Registry $registry,
        \Mirasvit\SearchElastic\Model\Engine $engine,
        Config $config
    ) {
        if (class_exists('Magento\CatalogSearch\Model\Indexer\Scope\State')) {
            $this->state = CompatibilityService::getObjectManager()
                ->get('Magento\CatalogSearch\Model\Indexer\Scope\State');
        } else {
            $this->state = null;
        }
        $this->registry = $registry;
        $this->engine = $engine;
        if (CompatibilityService::is22() || CompatibilityService::is23()) {
            $this->resolver = CompatibilityService::getObjectManager()
                ->create('Magento\CatalogSearch\Model\Indexer\Scope\ScopeProxy');
        } else {
            $this->resolver = CompatibilityService::getObjectManager()
                ->create('Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver');
        }
        $this->config = $config;
    }
    
    /**
     * @param mixed $subject
     * @param mixed $proceed
     * @param array $dimensions
     * @throws \Exception
     */
    public function aroundSwitchIndex($subject, $proceed, array $dimensions)
    {
        if ($this->config->getEngine() == 'elastic') {
            if ($this->state && \Magento\CatalogSearch\Model\Indexer\Scope\State::USE_TEMPORARY_INDEX === $this->state->getState()) {
                $index = $this->registry->registry(\Mirasvit\SearchElastic\Model\Indexer\IndexerHandler::ACTIVE_INDEX);
                $temporalIndexTable = $this->resolver->resolve($index, $dimensions);
                $this->state->useRegularIndex();
                $tableName = $this->resolver->resolve($index, $dimensions);
                $this->engine->removeIndex($tableName);
                $this->engine->moveIndex($temporalIndexTable, $tableName);
                $this->state->useTemporaryIndex();

                return ;
            }
        } else {
            return $proceed($dimensions);
        }
    }
}
