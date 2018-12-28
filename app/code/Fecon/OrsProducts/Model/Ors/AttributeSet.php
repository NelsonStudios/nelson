<?php

namespace Fecon\OrsProducts\Model\Ors;

/**
 * Class to create Ors attribute set and attribute group
 */
class AttributeSet extends \Fecon\OrsProducts\Model\AbstractAttributeSet
{

    /**
     * Get attribute set names to be created
     *
     * @return array
     */
    protected function getAttributeSetNames()
    {
        return [
            'ORS Products' => [
                'ORS Atributes' => [
                    'unspsc',
                    'upc',
                    'mfg_part_number',
                    'minimum_selling_quantity',
                    'std_pkg_quantity',
                    'price_date',
                    'level_1_customized',
                    'level_2_customized',
                    'level_3_customized',
                    'minimum_qty_for_level_1',
                    'your_promo_cost',
                    'promo_flyer_sell_price',
                    'promo_start_date',
                    'promo_end_date',
                    'price_change',
                    'closeout_flag',
                    'item_cube',
                    'web_uom',
                    'family',
                    'manufacturer',
                    'manufacturer_url',
                    'manufacturer_logo',
                    'hazmat',
                    'testing_and_approvals',
                    'minimum_order',
                    'standard_pack',
                    'prop_65_warning_required',
                    'prop_65_warning_label',
                    'prop_65_warning_message',
                    'prefix',
                    'brand_name',
                    'pricing_sku',
                    'multiples_required',
                    'sold_in_std_pkg',
                    'std_pkg_uom',
                    'price_unit',
                    'package',
                    'reference_price_type',
                    'promo_flag',
                    'volume_discount_flag',
                    'item_discount',
                    'product_group',
                    'orm_d_item',
                    'ansi_stock_uom_code',
                    'ansi_pri_or_sell_uom_code',
                    'ansi_std_pkg_uom_code',
                    'stocked_birmingham_al',
                    'stocked_chicago_il',
                    'stocked_charlotte_nc',
                    'stocked_cincinnati_oh',
                    'stocked_dallas_tx',
                    'stocked_denver_co',
                    'stocked_harrisburg_pa',
                    'stocked_houston_tx',
                    'stocked_st_paul_mn',
                    'stocked_muskogee_ok',
                    'stocked_orlando_fl',
                    'stocked_portland_or',
                    'stocked_bakersfield_ca',
                    'anchor_brand_cat_page',
                    'big_cat_page',
                    'construction_cat_page',
                    'industrial_cat_page',
                    'jansan_cat_page',
                    'most_popular_items_cat_page',
                    'oilfield_cat_page',
                    'rental_cat_page',
                    'safety_cat_page',
                    'welding_cat_page',
                    'features',
                    'attributes'
                ]
            ]
        ];
    }
}