<?php
namespace Fecon\AskAnExpert\Model\ResourceModel\Contact\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Fecon\AskAnExpert\Model\ResourceModel\Contact\Collection as QuoteCollection;

class Collection extends QuoteCollection implements SearchResultInterface
{
    protected $aggregations;

    /**
     * Constructor
     * 
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger       
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager 
     * @param [type]                                                       $mainTable    
     * @param [type]                                                       $eventPrefix  
     * @param [type]                                                       $eventObject  
     * @param [type]                                                       $resourceModel
     * @param string                                                       $model        
     * @param [type]                                                       $connection   
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource     
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        \Magento\Framework\DB\Adapter\AdapterInterface  $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * getAggregations
     * 
     * @return [type] [description]
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * [setAggregations description]
     * @return [type] [description]
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }


    /**
     * getAllIds
     * 
     * @return [type] [description]
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * getSearchCriteria
     * 
     * @return [type] [description]
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * [setSearchCriteria description]
     * @return \Magento\Framework\Api\SearchCriteriaInterface
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * getTotalCount
     * 
     * @return [type] [description]
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * [setTotalCount description]
     * @return [type] [description]
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * [setItems description]
     * @return [type] [description]
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
