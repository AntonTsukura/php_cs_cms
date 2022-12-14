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

use Tygh\Addons\Gdpr\ServiceProvider;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

Tygh::$app->register(new ServiceProvider());

fn_register_hooks(
    'update_product_notifications_post',
    'checkout_place_orders_pre_route',
    'save_cart_content_post',
    'user_logout_after',
    'update_profile',
    'update_company',
    'get_users',
    'user_init',
    'login_user_post',
    'smarty_function_script_after_formation',
    'get_addons_post',
    'update_payment_post'
);
