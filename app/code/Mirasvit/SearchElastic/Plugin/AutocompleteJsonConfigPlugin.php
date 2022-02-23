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



namespace Mirasvit\SearchElastic\Plugin;

use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\SearchElastic\Model\Config;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\Search\Request\Dimension;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SearchElastic\Model\Engine;

class AutocompleteJsonConfigPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * @var IndexScopeResolver
     */
    private $resolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Engine
     */
    private $engine;

    /**
     * AutocompleteJsonConfigPlugin constructor.
     * @param Config $config
     * @param IndexRepositoryInterface $indexRepository
     * @param IndexScopeResolver $resolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        IndexRepositoryInterface $indexRepository,
        IndexScopeResolver $resolver,
        StoreManagerInterface $storeManager,
        Engine $engine
    ) {
        $this->config = $config;
        $this->indexRepository = $indexRepository;
        $this->resolver = $resolver;
        $this->storeManager = $storeManager;
        $this->engine = $engine;
    }

    /**
     * @param mixed $subject
     * @param mixed $config
     * @return array
     */
    public function afterGenerate($subject, $config)
    {
        if ($config['engine'] !== 'elastic') {
            return $config;
        }

        $config = array_merge($config, $this->getEngineConfig());

        if (!isset($config['indexes'])) {
            throw new \UnexpectedValueException('Please specify indexes to display.
                "Popular suggestions" and "Products in categories" indexes are not processing by Search Autocomplete in Fast mode');
        }

        foreach ($this->storeManager->getStores() as $store) {
            foreach ($config['indexes'][$store->getId()] as $identifier => $data) {
                $data = array_merge($data, $this->getEngineIndexConfig(
                    $identifier,
                    new Dimension('scope', $store->getId())
                ));

                $config['indexes'][$store->getId()][$identifier] = $data;
            }
        }

        $config['esVersion'] = $this->engine->getEsVersion();

        return $config;
    }

    /**
     * @param string $identifier
     * @param Dimension $dimension
     * @return array
     */
    public function getEngineIndexConfig($identifier, $dimension)
    {
        $instance = $this->indexRepository->getInstance($identifier);

        $indexName = $this->config->getIndexName(
            $this->resolver->resolve($instance->getIndexName(), [$dimension])
        );

        $result = [];
        $result['index'] = $indexName;
        $result['fields'] = $instance->getAttributeWeights();

        return $result;
    }

    /**
     * @return array
     */
    public function getEngineConfig()
    {
        return [
            'host'      => $this->config->getHost(),
            'port'      => $this->config->getPort(),
            'available' => true,
        ];
    }
}
