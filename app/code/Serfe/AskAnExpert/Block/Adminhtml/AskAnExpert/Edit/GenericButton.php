<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Serfe\AskAnExpert\Block\Adminhtml\AskAnExpert\Edit;

use Magento\Backend\Block\Widget\Context;
//use Serfe\News\Api\NewsRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @param Context $context
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
        //$this->blockRepository = $blockRepository;
    }

    /**
     * Return CMS block ID
     *
     * @return int|null
     */
    public function getBlockId()
    {
/*
        {
            return $this->blockRepository->getById(
                $this->context->getRequest()->getParam('news_id')
            )->getId();
        }  */
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
