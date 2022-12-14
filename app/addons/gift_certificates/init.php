<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

if (!defined('BOOTSTRAP')) { die('Access denied'); }

Tygh::$app->register(new \Tygh\Addons\GiftCertificates\ServiceProvider());

fn_register_hooks(
    'pre_place_order',
    'place_order',
    'get_order_info',
    'change_order_status_post',
    'delete_order',
    'delete_cart_product',
    'generate_cart_id',
    array('calculate_cart', 200),
    'calculate_cart_items',
    array('exclude_products_from_calculation', 100),
    'form_cart',
    'allow_place_order',
    'save_cart_content_post',
    'extract_cart',
    'get_cart_item_types',
    'is_cart_empty',
    'exclude_from_shipping_calculation',
    'get_orders',
    'pre_add_to_cart',
    'delete_cart_product',
    'get_status_params_definition',
    'get_google_codes',
    'apply_google_codes',
    'form_google_codes_response',
    'google_coupons_calculation',
    'get_google_add_items',
    'reorder',
    'order_placement_routines',
    'display_promotion_input_field_post',
    'wishlist_get_count_post',
    'logo_types',
    'update_cart_by_data_post',
    'update_cart_products_post',
    'paypal_express_get_order_data',
    'quickbooks_export_order',
    'quickbooks_export_items',
    'change_order_status',
    'paypal_apply_discount_post',
    'template_document_order_context_init',
    'settings_variants_image_verification_use_for',
    'generate_ekeys_for_edp_pre',
    'rma_update_details_create_payout',
    'vendor_payout_details_builder_create_details_post',
    'vendor_payout_details_builder_create_updated_details_post'
);
if (fn_allowed_for('ULTIMATE')) {
    fn_register_hooks(
        'ult_check_store_permission'
    );
}
