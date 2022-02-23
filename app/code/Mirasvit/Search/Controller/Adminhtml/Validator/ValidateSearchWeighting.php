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


namespace Mirasvit\Search\Controller\Adminhtml\Validator;

use Mirasvit\Search\Controller\Adminhtml\Validator;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

use Magento\Catalog\Model\ProductFactory;

use Magento\Search\Model\SearchEngine;
use Magento\CatalogSearch\Model\Advanced\Request\BuilderFactory as RequestBuilderFactory;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;


use Mirasvit\Search\Index\Magento\Catalog\Product\Index as ProductIndex;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\Dimension;

class ValidateSearchWeighting extends Validator
{
    /**
     * @var SearchEngine
     */
    protected $searchEngine;
    /**
     * @var RequestBuilderFactory
     */
    protected $requestBuilderFactory;
    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolverInterface;
    /**
     * @var IndexScopeResolver
     */
    protected $indexScopeResolver;
    /**
     * @var ProductFactory
     */
    protected $productRepository;
    /**
     * @var ProductIndex
     */
    protected $productIndex;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool
     */
    private $proceedTest = true;
    /**
     * @var array
     */
    private $result = [];
    /**
     * @var string
     */
    private $status = self::STATUS_SUCCESS;
    /**
     * @var mixed
     */
    private $product;
    /**
     * @var string
     */
    private $query;
    /**
     * @var array
     */
    private $attributeWeight;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * ValidateSearchWeighting constructor.
     * @param JsonFactory $resultJsonFactory
     * @param SearchEngine $searchEngine
     * @param RequestBuilderFactory $requestBuilderFactory
     * @param ScopeResolverInterface $scopeResolverInterface
     * @param IndexScopeResolver $indexScopeResolver
     * @param ProductFactory $productRepository
     * @param ProductIndex $productIndex
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        SearchEngine $searchEngine,
        RequestBuilderFactory $requestBuilderFactory,
        ScopeResolverInterface $scopeResolverInterface,
        IndexScopeResolver $indexScopeResolver,
        ProductFactory $productRepository,
        ProductIndex $productIndex,
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->searchEngine = $searchEngine;
        $this->requestBuilderFactory = $requestBuilderFactory;
        $this->scopeResolverInterface = $scopeResolverInterface;
        $this->indexScopeResolver = $indexScopeResolver;
        $this->productRepository = $productRepository;
        $this->productIndex = $productIndex;
        $this->storeManager = $storeManager;
        
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $response = $this->resultJsonFactory->create();

        if ($this->getRequest()->getParam('q') && !empty($this->getRequest()->getParam('q'))) {
            $this->query = $this->getRequest()->getParam('q');
        } else {
            $this->status = self::STATUS_ERROR;
            $this->result[] = '<p>Please specify search term</p>';
            return $response->setData(['result' => implode('', $this->result),'status' => $this->status]);
        }

        if ($this->getRequest()->getParam('product_id') && !empty($this->getRequest()->getParam('product_id'))) {
            $productId = $this->getRequest()->getParam('product_id');
        } else {
            $this->status = self::STATUS_ERROR;
            $this->result[] = '<p>Please specify desired product ID</p>';
            return $response->setData(['result' => implode('', $this->result),'status' => $this->status]);
        }

        $requestBuilder = $this->requestBuilderFactory->create();
        
        $requestBuilder->bind('search_term', $this->query);

        $requestBuilder->bindDimension('scope', $this->scopeResolverInterface->getScope());

        $requestBuilder->setRequestName('catalogsearch_fulltext');

        $queryRequest = $requestBuilder->create();

        $collection = $this->searchEngine->search($queryRequest);
        
        $ids = [];
        foreach ($collection->getIterator() as $item) {
            $ids[] = $item->getId();
        }

        $this->attributeWeight = $this->productIndex->getAttributeWeights();
        $this->getProductEntity($productId);
        $this->getCatalogSearchData($ids);
        $this->isContainingSearchTerm();

        return $response->setData(['result' => implode('', $this->result),'status' => $this->status]);
    }

    /**
     * @param int $productId
     */
    private function getProductEntity($productId)
    {
        $this->product = $this->productRepository->create()->load($productId);
    }

    /**
     * @param array $ids
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCatalogSearchData($ids)
    {
        $this->result[] = '<p>Product data from catalogsearch_fulltext table: </p> <br/>';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName($this->getTableName()); //gives table name with prefix
        
        $productParents = $objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
            ->getParentIdsByChild($this->product->getId());
        
        $parentId = '';
        if (!empty($productParents)) {
            foreach ($productParents as $productParent) {
                if (in_array($productParent, $ids)) {
                    $parentId = $productParent;
                }
            }
        }
        $productId = $this->product->getId();
        if (!empty($parentId)) {
            $productId = $parentId;
            $this->result[] = '<p>Product '. $this->product->getId() .' is a child of '
                . $parentId .' , showing '. $parentId .' data </p>';
        }
        //Select Data from table
        $sql = 'Select * FROM ' . $tableName .' WHERE entity_id = '. $productId;
       
        // gives associated array, table fields as key in array.
        $catalogSearchData = $connection->fetchAll($sql);
        $searchAttributes = $this->prepareCatalogSearchData($catalogSearchData);
        $this->result[] = $this->buildAttributesTable($searchAttributes);
        
        return;
    }

    /**
     * @return bool
     */
    private function isContainingSearchTerm()
    {
        if (!$this->proceedTest) {
            return false;
        }

        $dataIndex = '';
        $searchAttributes = [];
        
        foreach ($this->attributeWeight as $key => $attributeWeight) {
            $attributeData = preg_replace('!\s+!', ' ', strip_tags($this->product->getData($key)));
            $attributeData = implode(' ', explode(' ', $attributeData));

            $dataIndex .= ' '. $attributeData;
            $searchAttributes[$key] = $attributeData;
        }
        $this->result[] = '<p> Search attributes values :</p>';
        $this->result[] = $this->buildAttributesTable($searchAttributes);
        
        $searhParts = explode(' ', $this->query);
        $searchedDataIndex = $dataIndex;
        foreach ($searhParts as $term) {
            $searchedDataIndex = str_ireplace($term, '<b>'. $term .'</b>', $searchedDataIndex);
        }

        if ($searchedDataIndex == $dataIndex) {
            $this->result[] = '<p>Search term "'. $this->query .'" didn`t match any attribute </p>';
        } else {
            $this->result[] = '<p>Search term "'. $this->query
                .'" has entires in data index, check the following: </p>';
            $this->result[] = '<p>'. $searchedDataIndex .'</p>';
        }

        return;
    }

    /**
     * @param array $searchAttributes
     * @return string
     */
    private function buildAttributesTable($searchAttributes)
    {
        $result = '<table style="border:1px solid">';
        foreach ($searchAttributes as $key => $attributeValue) {
                $result .= '<tr style="border:1px solid">'
                    .'<td style="border:1px solid">'. $key .'</td>'
                    .'<td style="border:1px solid">'. $attributeValue .'</td>'
                    .'<td style="border:1px solid; width:15%">'. $this->countSearchTermEntires($attributeValue) .'</td>'
                    .'<td style="border:1px solid; width:15%"> attribute weight is :'
                        .((isset($this->attributeWeight[$key]))? $this->attributeWeight[$key] : 'unset' ) .'</td>'
                    .'</tr>';
        }

        $result .= '</table><br />';

        return $result;
    }

    /**
     * @param mixed $catalogSearchData
     * @return array
     */
    private function prepareCatalogSearchData($catalogSearchData)
    {
        $result = [];
       
        foreach ($catalogSearchData as $searchDataEntity) {
            $attributeId = $this->productIndex->getAttributeCode($searchDataEntity['attribute_id']);
            $attributeData = preg_replace('!\s+!', ' ', strip_tags($searchDataEntity['data_index']));
                
            $result[$attributeId] = $attributeData;
        }

        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getTableName()
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->indexScopeResolver
            ->resolve('catalogsearch_fulltext', ["scope" => new Dimension('scope', $storeId)]);
    }

    /**
     * @param string $attributeValue
     * @return string
     */
    private function countSearchTermEntires($attributeValue)
    {
        $searhParts = explode(' ', $this->query);
        $qty = '';
        foreach ($searhParts as $term) {
            $qty .= '<p>'. substr_count(strtolower($attributeValue), strtolower($term))
                .' entire(s) of '. $term .' </p>';
        }

        return $qty;
    }
}
