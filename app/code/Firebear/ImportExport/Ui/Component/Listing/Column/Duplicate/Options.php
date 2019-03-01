<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Ui\Component\Listing\Column\Duplicate;

use Firebear\ImportExport\Model\Import\Address;
use Firebear\ImportExport\Model\Import\CmsPage;
use Firebear\ImportExport\Model\Import\Customer;
use Firebear\ImportExport\Model\Import\CustomerComposite;
use Firebear\ImportExport\Model\Import\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var Product
     */
    protected $productImportModel;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var CustomerComposite
     */
    protected $composite;

    /**
     * @var CmsPage
     */
    protected $cmsPage;

    /**
     * Options constructor.
     *
     * @param CollectionFactory $attributeFactory
     * @param Product $productImportModel
     * @param Customer $customer
     * @param Address $address
     * @param CustomerComposite $composite
     * @param CmsPage $cmsPage
     */
    public function __construct(
        CollectionFactory $attributeFactory,
        Product $productImportModel,
        Customer $customer,
        Address $address,
        CustomerComposite $composite,
        CmsPage $cmsPage
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->productImportModel = $productImportModel;
        $this->customer = $customer;
        $this->address = $address;
        $this->composite = $composite;
        $this->cmsPage = $cmsPage;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $newOptions = $this->productImportModel->getDuplicateFields();
        $newOptions = array_merge($newOptions, $this->customer->getDuplicateFields());
        $newOptions = array_merge($newOptions, $this->address->getDuplicateFields());
        $newOptions = array_merge($newOptions, $this->composite->getDuplicateFields());
        $newOptions = array_merge($newOptions, $this->cmsPage->getDuplicateFields());
        $this->options = array_unique($newOptions);

        return $this->options;
    }
}
