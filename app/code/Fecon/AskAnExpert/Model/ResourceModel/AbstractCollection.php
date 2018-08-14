<?php
namespace Fecon\AskAnExpert\Model\ResourceModel;

abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * 
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory [description]
     * @param \Psr\Log\LoggerInterface                                     $logger        [description]
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy [description]
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager  [description]
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection    [description]
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource      [description]
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }
    
    /**
     * addFieldToFilter
     * 
     * @param [string] $field
     * @param [string] $condition
     */
    public function addFieldToFilter($field, $condition = null)
    {
        return parent::addFieldToFilter($field, $condition);
    }
}
