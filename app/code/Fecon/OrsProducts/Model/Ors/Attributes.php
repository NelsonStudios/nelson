<?php

namespace Fecon\OrsProducts\Model\Ors;

/**
 * Class to create Ors attributes
 */
class Attributes extends \Fecon\OrsProducts\Model\AbstractAttributes
{

    /**
     * Get Text Attributes
     *
     * @return array
     */
    protected function getTextAttributes()
    {
        $orsAttributes = [
            'unspsc' => [
                'label' => 'UNSPSC'
            ],
            'upc' => [
                'label' => 'UPC'
            ],
            'mfg_part_number' => [
                'label' => 'MfgPartNumber'
            ],
            'minimum_selling_quantity' => [
                'label' => 'Minimum Selling Quantity'
            ],
            'std_pkg_quantity' => [
                'label' => 'Std. Pkg. Quantity'
            ],
            'price_date' => [
                'label' => 'Price Date'
            ],
            'level_1_customized' => [
                'label' => 'Level 1 Customized'
            ],
            'level_2_customized' => [
                'label' => 'Level 2 Customized'
            ],
            'level_3_customized' => [
                'label' => 'Level 3 Customized'
            ],
            'minimum_qty_for_level_1' => [
                'label' => 'Minimum Qty for Level 1'
            ],
            'your_promo_cost' => [
                'label' => 'Your Promo Cost'
            ],
            'promo_flyer_sell_price' => [
                'label' => 'Promo Flyer Sell Price'
            ],
            'promo_start_date' => [
                'label' => 'Promo Start Date'
            ],
            'promo_end_date' => [
                'label' => 'Promo End Date'
            ],
            'price_change' => [
                'label' => 'Price Change'
            ],
            'closeout_flag' => [
                'label' => 'Closeout Flag'
            ],
            'item_cube' => [
                'label' => 'Item Cube'
            ]
        ];
        $attributes = $this->mergeTextAttributes($orsAttributes);


        return $attributes;
    }

    /**
     * Get user defined dropdown attributes
     *
     * @return array
     */
    protected function getDropdownAttributes()
    {
        $orsAttributes = [
            'web_uom' => [
                'label' => 'WebUOM',
            ],
            'family' => [
                'label' => 'Family'
            ],
            'manufacturer_url' => [
                'label' => 'Manufacturer URL'
            ],
            'manufacturer_logo' => [
                'label' => 'Manufacturer Logo'
            ],
            'hazmat' => [
                'label' => 'Hazmat',
                'input' => 'boolean'
            ],
            'testing_and_approvals' => [
                'label' => 'TestingAndApprovals'
            ],
            'minimum_order' => [
                'label' => 'MinimumOrder'
            ],
            'standard_pack' => [
                'label' => 'StandardPack'
            ],
            'prop_65_warning_required' => [
                'label' => 'Prop 65 Warning Required',
                'input' => 'boolean'
            ],
            'prop_65_warning_label' => [
                'label' => 'Prop 65 Warning Label',
                'input' => 'boolean'
            ],
            'prop_65_warning_message' => [
                'label' => 'Prop 65 Warning Message'
            ],
            'prefix' => [
                'label' => 'Prefix'
            ],
            'brand_name' => [
                'label' => 'Brand Name'
            ],
            'pricing_sku' => [
                'label' => 'SKU'
            ],
            'multiples_required' => [
                'label' => 'Multiples Required',
                'input' => 'boolean'
            ],
            'sold_in_std_pkg' => [
                'label' => 'Sold in Std. Pkg.',
                'input' => 'boolean'
            ],
            'std_pkg_uom' => [
                'label' => 'Std. Pkg. UOM'
            ],
            'price_unit' => [
                'label' => 'Price Unit'
            ],
            'package' => [
                'label' => 'Package'
            ],
            'reference_price_type' => [
                'label' => 'Reference Price Type'
            ],
            'promo_flag' => [
                'label' => 'Promo Flag'
            ],
            'volume_discount_flag' => [
                'label' => 'Volume Discount Flag'
            ],
            'item_discount' => [
                'label' => 'Item Discount'
            ],
            'product_group' => [
                'label' => 'Product Group'
            ],
            'orm_d_item' => [
                'label' => 'ORM-D Item',
                'input' => 'boolean'
            ],
            'ansi_stock_uom_code' => [
                'label' => 'ANSI Stock UOM Code'
            ],
            'ansi_pri_or_sell_uom_code' => [
                'label' => 'ANSI pricing or selling UOM Code'
            ],
            'ansi_std_pkg_uom_code' => [
                'label' => 'ANSI Std. Pkg UOM Code'
            ],
            'stocked_birmingham_al' => [
                'label' => 'Stocked Birmingham AL'
            ],
            'stocked_chicago_il' => [
                'label' => 'Stocked Chicago IL'
            ],
            'stocked_charlotte_nc' => [
                'label' => 'Stocked Charlotte NC'
            ],
            'stocked_cincinnati_oh' => [
                'label' => 'Stocked Cincinnati OH'
            ],
            'stocked_dallas_tx' => [
                'label' => 'Stocked Dallas TX'
            ],
            'stocked_denver_co' => [
                'label' => 'Stocked Denver CO'
            ],
            'stocked_harrisburg_pa' => [
                'label' => 'Stocked Harrisburg PA'
            ],
            'stocked_houston_tx' => [
                'label' => 'Stocked Houston TX'
            ],
            'stocked_st_paul_mn' => [
                'label' => 'Stocked St. Paul MN'
            ],
            'stocked_muskogee_ok' => [
                'label' => 'Stocked Muskogee OK'
            ],
            'stocked_orlando_fl' => [
                'label' => 'Stocked Orlando FL'
            ],
            'stocked_portland_or' => [
                'label' => 'Stocked Portland OR'
            ],
            'stocked_bakersfield_ca' => [
                'label' => 'Stocked Bakersfield CA'
            ],
            'anchor_brand_cat_page' => [
                'label' => 'Anchor Brand Catalog Page #'
            ],
            'big_cat_page' => [
                'label' => 'Big Catalog Page #'
            ],
            'construction_cat_page' => [
                'label' => 'Construction Catalog Page #'
            ],
            'industrial_cat_page' => [
                'label' => 'Industrial Catalog Page #'
            ],
            'jansan_cat_page' => [
                'label' => 'Jan/San Catalog Page #'
            ],
            'most_popular_items_cat_page' => [
                'label' => 'Most Popular Items Catalog Page #'
            ],
            'oilfield_cat_page' => [
                'label' => 'Oilfield Catalog Page #'
            ],
            'rental_cat_page' => [
                'label' => 'Rental Catalog Page #'
            ],
            'safety_cat_page' => [
                'label' => 'Safety Catalog Page #'
            ],
            'welding_cat_page' => [
                'label' => 'Welding Catalog Page #'
            ]
        ];

        $attributes = $this->mergeDropdownAttributes($orsAttributes);

        return $attributes;
    }

    /**
     * Get user defined multiselect attributes
     *
     * @return array
     */
    protected function getMultiselectAttributes()
    {
        $orsAttributes = [
            'features' => [
                'label' => 'Features',
            ]
        ];

        $attributes = $this->mergeMultiselectAttributes($orsAttributes);

        return $attributes;
    }

    /**
     * Get user defined textarea attributes
     *
     * @return array
     */
    protected function getTextareaAttributes()
    {
        $orsAttributes = [
            'attributes' => [
                'label' => 'Attributes',
            ]
        ];

        $attributes = $this->mergeTextareaAttributes($orsAttributes);

        return $attributes;
    }
}