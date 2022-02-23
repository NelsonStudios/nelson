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



namespace Mirasvit\SearchElastic\Index;

use Mirasvit\Search\Api\Data\Index\DataMapperInterface;
use Mirasvit\Search\Api\Repository\IndexRepositoryInterface;
use Mirasvit\Search\Api\Data\IndexInterface;

class DataMapper implements DataMapperInterface
{
    /**
     * @var IndexRepositoryInterface
     */
    private $indexRepository;

    /**
     * DataMapper constructor.
     *
     * @param IndexRepositoryInterface $indexRepository
     */
    public function __construct(
        IndexRepositoryInterface $indexRepository
    ) {
        $this->indexRepository = $indexRepository;
    }

    /**
     * @param array                                         $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param IndexInterface      $index
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function map(array $documents, $dimensions, $index)
    {
        $instance = $this->indexRepository->getInstance($index);

        foreach ($documents as $id => $doc) {
            $map = [
                'id'                       => $id,
                $instance->getPrimaryKey() => $id,
            ];

            foreach ($doc as $attribute => $value) {
                if (is_int($attribute)) {
                    $attribute = $instance->getAttributeCode($attribute);
                }
                if (isset($map[$attribute])) {
                    $map[$attribute] .= ' ' . $value;
                } else {
                    $map[$attribute] = $value;
                }
            }

            $documents[$id] = $map;
        }

        return $documents;
    }
}
