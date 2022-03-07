<?php
/**
 * @author Elsner Team
 * @copyright Copyright (c) 2021 Elsner Technologies Pvt. Ltd (https://www.elsner.com/)
 * @package Elsnertech_Paytrace
 */

namespace Elsnertech\Paytrace\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class PaytraceConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $_paytreApi;

    protected $_methodCode = Paytrace::PAYMENT_METHOD_APYTRACE_CODE;

    public function __construct(
        \Elsnertech\Paytrace\Model\Api\Api $paytreApi
    ) {
        $this->_paytreApi = $paytreApi;
    }

    /**
     * getConfig function to return cofig data to payment renderer.
     *
     * @return []
     */
    public function getConfig()
    {
        /**
         * $config array to pass config data to payment renderer component.
         *
         * @var array
         */
        $config = [
            'payment' => [
                'paytrace' => [
                    'paytrace_vault' => $this->getVaultEnable()
                ],
            ],
        ];

        return $config;
    }

    public function getVaultEnable()
    {
        
        if ($this->_paytreApi->getCustomerSession()->isLoggedIn() && $this->_paytreApi->getPaytraceVaultEnable()) {
            return 1;
        } else {
            return 0;
        }
    }
    /**
     * getSavedCards function to get customers cards json data.
     *
     * @return json
     */
    public function getSavedCards()
    {
        $cardsArray = [];
        $cards = $this->_paytreApi->getSavedCards();
        if ($cards) {
            foreach ($cards as $card) {
                    array_push(
                        $cardsArray,
                        [
                            'exp_month' => $card->getCcMonth(),
                            'exp_year' => $card->getCcYear(),
                            'paytrace_customer_id' => $card->getPaytraceCustomerId(),
                            'last4' => '****'.$card->getCcNumber(),
                        ]
                    );
            }
        }

        return json_encode($cardsArray, true);
    }
}
