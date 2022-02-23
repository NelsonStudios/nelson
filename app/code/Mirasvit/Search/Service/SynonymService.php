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

use Mirasvit\Search\Api\Data\SynonymInterface;
use Mirasvit\Search\Api\Repository\SynonymRepositoryInterface;
use Mirasvit\Search\Api\Service\CloudServiceInterface;
use Mirasvit\Search\Api\Service\SynonymServiceInterface;

class SynonymService implements SynonymServiceInterface
{
    /**
     * @var SynonymRepositoryInterface
     */
    private $synonymRepository;

    /**
     * @var CloudServiceInterface
     */
    private $cloudService;

    /**
     * @var array
     */
    private static $complexSynonyms;

    /**
     * SynonymService constructor.
     * @param SynonymRepositoryInterface $synonymRepository
     * @param CloudServiceInterface $cloudService
     */
    public function __construct(
        SynonymRepositoryInterface $synonymRepository,
        CloudServiceInterface $cloudService
    ) {
        $this->synonymRepository = $synonymRepository;
        $this->cloudService      = $cloudService;
    }

    /**
     * {@inheritdoc}
     */
    public function getSynonyms(array $terms, $storeId)
    {
        $result = [];

        $collection = $this->synonymRepository->getCollection();

        foreach ($terms as $term) {
            $collection->getSelect()
                ->orWhere('term = ?', $term);
        }

        /** @var SynonymInterface $model */
        foreach ($collection as $model) {
            $synonyms = explode(',', $model->getSynonyms());

            foreach ($terms as $term) {
                if ($model->getTerm() === $term) {
                    $result[$term] = $synonyms;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getComplexSynonyms($storeId)
    {
        if (!self::$complexSynonyms) {
            self::$complexSynonyms = $this->synonymRepository->getCollection();

            self::$complexSynonyms->getSelect()
                ->where("term like '% %'");
        }

        return self::$complexSynonyms;
    }

    /**
     * {@inheritdoc}
     */
    public function import($file, $storeIds)
    {
        $result = [
            'synonyms' => 0,
            'total'    => 0,
            'errors'   => 0,
            'message'  => '',
        ];

        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = $this->cloudService->get('search', 'synonym', $file);
        }

        if (!$content) {
            yield false;
        } else {
            if (strlen($content) > 120000 && php_sapi_name() != "cli") {
                $result['message'] = __('File is too large. Please use CLI interface (bin/magento mirasvit:search:synonym --file EN.yaml --store 1)');
                yield $result;
            } else {
                $synonyms = Yaml\Yaml::decode($content);

                if (!is_array($storeIds)) {
                    $storeIds = [$storeIds];
                }

                foreach ($storeIds as $storeId) {
                    $result['total'] = count($synonyms);

                    yield $result;

                    foreach ($synonyms as $synonym) {
                        try {
                            $model = $this->synonymRepository->create()
                                ->setTerm($synonym['term'])
                                ->setSynonyms($synonym['synonyms'])
                                ->setStoreId($storeId);

                            $this->synonymRepository->save($model);

                            $result['synonyms']++;
                        } catch (\Exception $e) {
                            $result['errors']++;

                            if (strripos($e->getMessage(), '(')=== false) {
                                $result['message'] = $e->getMessage();
                            } else {
                                $result['message'] = substr($e->getMessage(), 0, strripos($e->getMessage(), '('));
                            }
                        }

                        yield $result;
                    }
                }

                yield $result;
            }
        }
    }
}
