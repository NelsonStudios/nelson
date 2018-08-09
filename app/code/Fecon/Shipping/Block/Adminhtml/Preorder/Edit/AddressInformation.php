<?php

namespace Fecon\Shipping\Block\Adminhtml\Preorder\Edit;

use Magento\Framework\Exception\LocalizedException;
use Fecon\Shipping\Api\Data\PreorderInterface;

/**
 * Block to display address Information
 *
 * 
 */
class AddressInformation extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface 
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Helper\Address 
     */
    protected $addressHelper;

    /**
     * @var \Magento\Framework\Registry 
     */
    protected $coreRegistry;

    /**
     * Address mapper
     *
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        array $data = array()
    ) {
        $this->addressRepository = $addressRepository;
        $this->addressHelper = $addressHelper;
        $this->coreRegistry = $coreRegistry;
        $this->addressMapper = $addressMapper;

        parent::__construct($context, $data);
    }

    /**
     * Return preorder's address
     *
     * @return PreorderInterface|null
     */
    protected function getAddress()
    {
        $preorder = $this->coreRegistry->registry('fecon_shipping_preorder');
        $addressId = $preorder->getData(PreorderInterface::ADDRESS_ID);
        try {
            $address = $this->addressRepository->getById($addressId);
        } catch (LocalizedException $e) {
            $this->_logger->error('Cannot get preorder address in AddressInformation block');
            $address = null;
        }


        return $address;
    }

    /**
     * Retrieve address html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getAddressHtml()
    {
        $address = $this->getAddress();

        if ($address === null) {
            return __('The preorder does not have address.');
        }

        return $this->addressHelper->getFormatTypeRenderer(
                'html'
            )->renderArray(
                $this->addressMapper->toFlatArray($address)
        );
    }
}