<?php

namespace IWD\Opc\Block\Onepage;

use Magento\Checkout\Block\Onepage\Success as CheckoutSuccess;
use IWD\Opc\Helper\Data as OpcHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Sales\Model\Order\Config;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Customer\Model\Registration;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session\Proxy as CustomerSession;
use Magento\Sales\Model\Order\Address\Validator;
use Magento\Sales\Api\OrderRepositoryInterface;

class Success extends CheckoutSuccess
{
    /**
     * @var Registration
     */
    public $registration;

    /**
     * @var AccountManagementInterface
     */
    public $accountManagement;

    /**
     * @var CustomerSession
     */
    public $customerSession;

    /**
     * @var Validator
     */
    public $addressValidator;

    /**
     * @var OrderRepositoryInterface
     */
    public $orderRepository;

    public $opcHelper;

    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Config $orderConfig,
        HttpContext $httpContext,
        Registration $registration,
        AccountManagementInterface $accountManagement,
        CustomerSession $customerSession,
        Validator $addressValidator,
        OrderRepositoryInterface $orderRepository,
        OpcHelper $opcHelper,
        array $data = []
    ) {
        $data['module_name'] = 'Magento_Checkout';
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->accountManagement = $accountManagement;
        $this->registration = $registration;
        $this->customerSession = $customerSession;
        $this->addressValidator = $addressValidator;
        $this->orderRepository = $orderRepository;
        $this->opcHelper = $opcHelper;
    }


    /**
     * Prepares block data
     *
     * @return void
     */
    protected function prepareBlockData()
    {
        $orderId = $this->_checkoutSession->getLastOrderId();
        $order = $this->orderRepository->get($orderId);

        $this->addData(
            [
                'is_order_visible' => $this->isVisible($order),
                'view_order_url' => $this->getUrl(
                    'sales/order/view/',
                    ['order_id' => $order->getEntityId()]
                ),
                'print_url' => $this->getUrl(
                    'sales/order/print',
                    ['order_id' => $order->getEntityId()]
                ),
                'can_print_order' => $this->isVisible($order),
                'can_view_order'  => $this->canViewOrder($order),
                'order_id'  => $order->getIncrementId()
            ]
        );
    }

    protected function _toHtml()
    {
        if ($this->opcHelper->isShowSuccessPage() &&
            $this->opcHelper->isEnable() && $this->opcHelper->isModuleOutputEnabled('IWD_Opc')) {
            $layout = $this->getLayout();
            $layout->getBlock('checkout.registration');
            $layout->unsetElement('checkout.registration');
            $layout->getBlock('page.main.title');
            $layout->unsetElement('page.main.title');
            $this->setTemplate('IWD_Opc::success/success.phtml');
            if ($this->getNameInLayout() === 'checkout.success.print.button') {
                return '';
            }
        }

        return parent::_toHtml();
    }

    public function getCreateAccountUrl()
    {
        return $this->getUrl('checkout/account/create');
    }

    public function getCustomerAccountUrl()
    {
        return $this->getUrl('customer/account');
    }

    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getName();
    }

    public function getEmailAddress()
    {
        return $this->_checkoutSession->getLastRealOrder()->getCustomerEmail();
    }

    public function isShowRegistrationForm()
    {
        if ($this->isCustomerLoggedIn()
            || !$this->registration->isAllowed()
            || !$this->accountManagement->isEmailAvailable($this->getEmailAddress())
            || !$this->validateAddresses()
        ) {
            return false;
        }

        return true;
    }

    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function validateAddresses()
    {
        $order = $this->orderRepository->get($this->_checkoutSession->getLastOrderId());
        $addresses = $order->getAddresses();
        foreach ($addresses as $address) {
            $result = $this->addressValidator->validateForCustomer($address);
            if (is_array($result) && !empty($result)) {
                return false;
            }
        }

        return true;
    }
}
