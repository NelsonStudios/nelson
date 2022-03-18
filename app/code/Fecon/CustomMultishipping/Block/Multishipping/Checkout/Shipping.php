<?php

declare(strict_types=1);

namespace Fecon\CustomMultishipping\Block\Multishipping\Checkout;

use ClassyLlama\AvaTax\Exception\AddressValidateException;
use ClassyLlama\AvaTax\Exception\AvataxConnectionException;
use ClassyLlama\AvaTax\Helper\Multishipping\Checkout\AddressValidation;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\DataObject\GridFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template\Context;
use Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping;
use Magento\Tax\Helper\Data;

/**
 * Class Shipping
 *
 * @package Fecon\CustomMultishipping\Block\Multishipping\Checkout
 */
class Shipping extends \Fecon\CustomMultishipping\Block\Checkout\Shipping
{


    /**
     * @var AddressValidation
     */
    private $addressValidation;

    /**
     * Shipping constructor.
     *
     * @param Context $context
     * @param GridFactory $filterGridFactory
     * @param Multishipping $multishipping
     * @param Data $taxHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param AddressValidation $addressValidation
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Fecon\CustomMultishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        PriceCurrencyInterface $priceCurrency,
        \Psr\Log\LoggerInterface $logger,
        AddressValidation $addressValidation,
        array $data = []
    ) {
        parent::__construct($context, $filterGridFactory, $multishipping, $taxHelper, $priceCurrency,$logger, $data);
        $this->addressValidation = $addressValidation;
    }

    /**
     * @return mixed
     */
    public function isValidationEnabled()
    {
        return $this->addressValidation->isValidationEnabled();
    }

    /**
     * @param AddressInterface $address
     * @return array
     * @throws AddressValidateException
     * @throws AvataxConnectionException
     * @throws LocalizedException
     */
    public function validateAddress($address): array
    {
        return $this->addressValidation->validateAddress($address);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCode(): string
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
