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

class PaytracevaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $_methodCode = Paytracevault::PAYMENT_METHOD_APYTRACEVAULT_CODE;

    /**
     * @var Checkmo
     */
    protected $_method;

    /**
     * @var Escaper
     */
    protected $_escaper;

    protected $_paytreApi;
    protected $_assetRepo;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Elsnertech\Paytrace\Model\Api\Api $paytreApi,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        Escaper $escaper
    ) {
        $this->_escaper = $escaper;
        $this->_paytreApi = $paytreApi;
        $this->_assetRepo = $assetRepo;
        $this->_method = $paymentHelper->getMethodInstance($this->_methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->_method->isAvailable() ? [
            'payment' => [
                'paytracevault' => [
                    'mailingAddress' => $this->getMailingAddress(),
                    'payableTo' => $this->getPayableTo(),
                    'saved_cards' => $this->getSavedCards(),
                ],
            ],
        ] : [];
    }

    public function getSavedCards()
    {
        $cardsArray = [];
        $cards = $this->_paytreApi->getSavedCardArray();
        if (!empty($cards)) {
            foreach ($cards as $card) {
                    $imageName = strtolower($card['cc_type']).'.png';
                    array_push(
                        $cardsArray,
                        [
                            'exp_month' => $card['cc_month'],
                            'exp_year' => $card['cc_year'],
                            'paytrace_customer_id' => $card['paytrace_customer_id'],
                            'last4' => '****'.$card['cc_number'],
                            'card_image' => $this->_assetRepo->getUrl('Magento_Payment::images/cc/'.$imageName)
                        ]
                    );
            }
        }

        return json_encode($cardsArray, true);
    }

    /**
     * Get mailing address from config
     *
     * @return string
     */
    protected function getMailingAddress()
    {
        return nl2br($this->_escaper->escapeHtml($this->_method->getMailingAddress()));
    }

    /**
     * Get payable to from config
     *
     * @return string
     */
    protected function getPayableTo()
    {
        return $this->_method->getPayableTo();
    }
}
