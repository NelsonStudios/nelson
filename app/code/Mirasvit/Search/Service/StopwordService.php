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



namespace Mirasvit\Search\Service;

use Mirasvit\Search\Api\Repository\StopwordRepositoryInterface;
use Mirasvit\Search\Api\Service\CloudServiceInterface;
use Mirasvit\Search\Api\Service\StopwordServiceInterface;

class StopwordService implements StopwordServiceInterface
{
    /**
     * @var StopwordRepositoryInterface
     */
    private $stopwordRepository;

    /**
     * @var CloudServiceInterface
     */
    private $cloudService;

    /**
     * StopwordService constructor.
     * @param StopwordRepositoryInterface $stopwordRepository
     * @param CloudServiceInterface $cloudService
     */
    public function __construct(
        StopwordRepositoryInterface $stopwordRepository,
        CloudServiceInterface $cloudService
    ) {
        $this->stopwordRepository = $stopwordRepository;
        $this->cloudService       = $cloudService;
    }

    /**
     * {@inheritdoc}
     */
    public function isStopword($term, $storeId)
    {
        $collection = $this->stopwordRepository->getCollection()
            ->addFieldToFilter('term', $term)
            ->addFieldToFilter('store_id', [0, $storeId]);

        return $collection->getSize() ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function import($file, $storeIds)
    {
        $result = [
            'stopwords'     => 0,
            'errors'        => 0,
            'error_message' => '',
        ];

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = $this->cloudService->get('search', 'stopword', $file);
        }
        if (!$content) {
            yield $result;
        } else {
            $stopwords = Yaml\Yaml::decode($content);

            if (!is_array($storeIds)) {
                $storeIds = [$storeIds];
            }
            foreach ($storeIds as $storeId) {
                foreach ($stopwords as $stopword) {
                    try {
                        $stopword = $this->stopwordRepository->create()
                            ->setTerm($stopword)
                            ->setStoreId($storeId);

                        $this->stopwordRepository->save($stopword);

                        $result['stopwords']++;
                    } catch (\Exception $e) {
                        $result['errors']++;

                        if (strripos($e->getMessage(), '(')=== false) {
                            $result['error_message'] = $e->getMessage();
                        } else {
                            $result['error_message'] = substr($e->getMessage(), 0, strripos($e->getMessage(), '('));
                        }
                    }
                }
            }

            yield $result;
        }
    }
}
