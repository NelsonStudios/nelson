<?php


namespace Fecon\Sso\Controller\Idp;

use Magento\Framework\Controller\ResultFactory;

/**
 * Controller that returns Saml Reponse after login
 */
class SamlResponse extends \Fecon\Sso\Controller\AbstractController
{

    /**
     * @var \Magento\Customer\Model\Session 
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Fecon\Sso\Api\SsoInterfaceFactory $ssoFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $resultPageFactory, $ssoFactory);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->isDocumotoUser()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectUrl = $this->_url->getUrl('join-our-dealers-program');
            $result->setUrl($redirectUrl);

            return $result;
        }

        return $this->resultPageFactory->create();
    }

    /**
     * Check if there's a logged-in user, and if it is a Documoto user as well
     *
     * @return bool
     */
    protected function isDocumotoUser()
    {
        $customer = $this->getCustomer();
        $isDocumotoUser = false;
        if ($customer && $customer->getCustomAttribute('is_documoto_user')) {
            $isDocumotoUser = (bool) $customer->getCustomAttribute('is_documoto_user')->getValue();
        }

        return $isDocumotoUser;
    }

    /**
     * Get current logged-in customer (if there's one)
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    protected function getCustomer()
    {
        $customer = null;
        $customerId = null;
        if ($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getCustomer()->getId();
        }

        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
            } catch (\Exception $ex) {
            }
        }

        return $customer;
    }
}