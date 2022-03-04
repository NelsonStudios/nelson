<?php

namespace Amasty\Rma\Controller\Guest;

use Amasty\Rma\Api\CustomerRequestRepositoryInterface;
use Amasty\Rma\Model\ConfigProvider;
use Magento\Framework\App\Action\Context;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CustomerRequestRepositoryInterface
     */
    private $customerRequestRepository;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CustomerRequestRepositoryInterface $customerRequestRepository,
        ConfigProvider $configProvider,
        Context $context
    ) {
        parent::__construct($context);
        $this->customerRequestRepository = $customerRequestRepository;
        $this->configProvider = $configProvider;
    }

    public function execute()
    {
        if ($requestHash = $this->getRequest()->getParam('request')) {
            if ($this->customerRequestRepository->closeRequest($requestHash)) {
                $this->messageManager->addSuccessMessage(__('Return Request successfully closed.'));

                return $this->_redirect(
                    $this->configProvider->getUrlPrefix() . '/guest/view',
                    ['request' => $requestHash]
                );
            }
        }

        return $this->_redirect($this->configProvider->getUrlPrefix() . '/request/index');
    }
}
