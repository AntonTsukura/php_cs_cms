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

use Tygh\BlockManager\Block;
use Tygh\Enum\SiteArea;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_rss_feed($block_data)
{
    $items['action'] = $block_data['block_data']['block_id'];

    return array($items, '');
}

function fn_get_company_logo_url($data)
{
    $logos = fn_get_logos();
    $image = fn_image_to_display($logos['theme']['image'], 144); //max width = 144, height = 400 according to rss specification

    if (!empty($image)) {
        $image = [
            'url' => !empty($image['image_path']) ? $image['image_path'] : (Registry::get('config.http_location') . '/images/no_image.png'),
            'title' => $data['title'],
            'link' => $data['link'],
            'width' => !empty($image['width']) ? $image['width'] : '',
            'height' => !empty($image['height']) ? $image['height'] : '',
        ];
    }

    return $image;
}

function fn_generate_rss($items_data, $additional_data = array())
{
    if (empty($additional_data['title']) || empty($additional_data['description']) || empty($additional_data['link'])) {
        return '';
    }

    $default_additional_data = array (
        'copyright' => (fn_date_format(time(), '%Y') != Registry::get('settings.Company.company_start_year')) ? (Registry::get('settings.Company.company_start_year') . ' - ' . fn_date_format(time(), '%Y') . ' ' . Registry::get('settings.Company.company_name')) : '',
        'language' => CART_LANGUAGE,
        'managingEditor' => Registry::get('settings.rss_feed.general.managing_editor'),
        'ttl' => 30,
        'image' => fn_get_company_logo_url($additional_data),
        'lastBuildDate' => time(),
    );

    $channel_data = array_merge($default_additional_data, $additional_data);

    $channel_data['lastBuildDate'] = fn_format_rss_time($channel_data['lastBuildDate']);

    $rss = '<?xml version="1.0" encoding="utf-8" ?>' . "\n";
    $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
    $rss .= '<channel>' . "\n";
    $rss .= '<atom:link href="' . Registry::get('config.current_location') . '/' . htmlspecialchars(Registry::get('config.current_url')) . '" rel="self" type="application/rss+xml" />' . "\n";
    $rss .= fn_array_to_xml($channel_data) . "\n";
    if (!empty($items_data)) {
        foreach ($items_data as $item) {
            $rss .= '<item>' . "\n";
            $rss .= fn_array_to_xml($item);
            $rss .= '</item>' . "\n";
        }
    } else {
        $rss .= '<item>' . "\n";
        $rss .= '<title>' . __('no_data_found', '', $channel_data['language']) . '</title>';
        $rss .= '<description>' . __('no_data_found', '', $channel_data['language']) . '</description>';
        $rss .= '</item>' . "\n";
    }
    $rss .= '</channel>' . "\n";
    $rss .= '</rss>';

    return $rss;
}

function fn_format_rss_time($timestamp)
{
    return date(DATE_RSS, $timestamp);
}

function fn_format_products_items($products, $params = array(), $lang_code = CART_LANGUAGE)
{
    $items_data = array();

    $default_params = array (
        'max_item' => Registry::get('addons.rss_feed.category_max_products_items'),
        'rss_sort_by' => 'U',
        'rss_display_image' => 'Y',
        'rss_display_price' => 'Y',
        'rss_display_original_price' => 'Y',
        'rss_display_add_to_cart' => 'Y'
    );

    $params = array_merge($default_params, $params);

    foreach ($products as $key => $product_data) {
        //format enclosure field for fn_array_to_xml function (enclosure@url=http://url.com@length=50000@type=image/jpeg)
        $enclosure = [
            'enclosure',
            'url=' . fn_format_image_url($product_data),
            'length=50000',
            'type=image/jpeg'
        ];
        $enclosure = implode('@', $enclosure);
        $items_data[$key] = [
            'title'       => $product_data['product'],
            'link'        => fn_url('products.view?product_id=' . $product_data['product_id'], SiteArea::STOREFRONT, 'current', $lang_code),
            'guid'        => fn_url('products.view?product_id=' . $product_data['product_id'], SiteArea::STOREFRONT, 'current', $lang_code),
            'pubDate'     => fn_format_rss_time(($params['rss_sort_by'] === 'U') ? $product_data['updated_timestamp'] : $product_data['timestamp']),
            'description' => fn_generate_product_description($product_data, $params, $lang_code),
            $enclosure    => '',
        ];
    }

    return $items_data;
}

function fn_format_image_url($product_data = array())
{
    $image_url = '';
    if (!empty($product_data['main_pair'])) {
        $image_data = fn_image_to_display($product_data['main_pair'], Registry::get('settings.Thumbnails.product_lists_thumbnail_width'), Registry::get('settings.Thumbnails.product_lists_thumbnail_height'));
        $image_url = !empty($image_data['image_path']) ? $image_data['image_path'] : '';
    }

    return $image_url;
}

function fn_generate_product_description($product_data, $params, $lang_code)
{
    $product_url = fn_url('products.view?product_id=' . $product_data['product_id'], 'C', 'current', $lang_code);
    $currencies = Registry::get('currencies');
    $currency_symbol = $currencies[CART_PRIMARY_CURRENCY]['symbol'];

    $description = '';

    if (!empty($params['rss_display_image']) && $params['rss_display_image'] == 'Y') {
        $image_url = fn_format_image_url($product_data);
        $description .= <<<EOT
<div><a href="{$product_url}"><img src="{$image_url}"></a></div>
EOT;
    }

    if (!empty($params['rss_display_sku']) && $params['rss_display_sku'] == 'Y') {
        if (!empty($product_data['product_code'])) {
            $sku = '<b>' . __('sku') . '#:</b> ' . $product_data['product_code'];
            $description .= <<<EOT
<div>{$sku}</div>
EOT;
        }
    }

    if (!empty($params['rss_display_price']) && $params['rss_display_price'] == 'Y') {
        if (!empty($product_data['price'])) {
            $discounted_price = fn_apply_price_discounts($product_data['price'], $product_data['product_id']);
            if ($discounted_price != fn_format_price($product_data['price'], CART_PRIMARY_CURRENCY, null, false)) {
                $price = '<b>' . __('discounted_price') . ':</b> ' . $currency_symbol . fn_apply_price_discounts($product_data['price'], $product_data['product_id']);
                $description .= <<<EOT
<div>{$price}</div>
EOT;
            }
        }
    }

    if (!empty($params['rss_display_original_price']) && $params['rss_display_original_price'] == 'Y') {
        if (!empty($product_data['price'])) {
            $original_price = '<b>' . __('price') . ':</b> ' . $currency_symbol . fn_format_price($product_data['price'], CART_PRIMARY_CURRENCY, null, false);
            $description .= <<<EOT
<div>{$original_price}</div>
EOT;
        }
    }

    if (!empty($params['rss_display_add_to_cart']) && $params['rss_display_add_to_cart'] == 'Y') {
        //hide add to cart button if anonymous shopping disabled
        if (Registry::get('settings.Checkout.allow_anonymous_shopping') == 'allow_shopping') {
            $add_to_cart_url = fn_url('rss.add_to_cart?product_id=' . $product_data['product_id'] . '&lang=' . $lang_code, 'C', 'current', $lang_code);
            $add_to_cart = '<a href="' . $add_to_cart_url . '" rel="nofollow">' . __('add_to_cart') . '</a>';
            $description .= <<<EOT
<div>{$add_to_cart}</div>
EOT;
        }
    }

    return $description;
}

function fn_apply_price_discounts($product_price, $product_id = 0)
{
    $auth = fn_fill_auth();
    $product = fn_get_product_data($product_id, $auth, CART_LANGUAGE, '', true, false, false, false);
    fn_promotion_apply('catalog', $product, $auth);
    $_discount = 0;
    if (!empty($product['discount'])) {
        $_discount = $product['discount'];
    }

    return fn_format_price($product_price - $_discount, CART_PRIMARY_CURRENCY, null, false);
}

function fn_format_categories_items($params, $lang_code)
{
    $items_data = $additional_data = $timestamps = array();

    if (empty($params['category_id'])) {
        return array($items_data, $additional_data);
    } else {
        $params['cid'] = $params['category_id'];
        $params['sort_by'] = 'updated_timestamp';
        $params['sort_order'] = 'desc';
        if (Registry::get('settings.General.show_products_from_subcategories') == 'Y') {
            $params['subcats'] = 'Y';
        }
    }

    $category_data = fn_get_category_data($params['category_id'], $lang_code, '*');

    list($products, ) = fn_get_products($params, Registry::get('addons.rss_feed.category_max_products_items'));
    fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true, 'get_features' => false));

    $additional_data['title'] = $category_data['category'];
    $additional_data['description'] = strip_tags(!empty($category_data['description']) ? $category_data['description'] : $additional_data['title']) ?: $additional_data['title'];
    $additional_data['link'] = fn_url('categories.view?category_id=' . $params['category_id'], 'C', 'current', $lang_code);
    $additional_data['language'] = $lang_code;
    $additional_data['lastBuildDate'] = !empty($products[0]['updated_timestamp']) ? $products[0]['updated_timestamp'] : TIME;

    $items_data = fn_format_products_items($products, array(), $lang_code);

    return array($items_data, $additional_data);
}

/**
 * Prepares feed items and properties
 *
 * @param array $params Request parameters
 * @param string $lang_code Two-letter language code
 *
 * @return array Array, containing feed items and feed properties
 */
function fn_rssf_get_items($params, $lang_code = CART_LANGUAGE)
{
    $items_data = $additional_data = $block_data = array();

    if (!empty($params['bid']) && !empty($params['sid']) && empty($params['category_id'])) {
        $block_data = Block::instance()->getById($params['bid'], $params['sid'], array(), $lang_code);

        if (!empty($block_data['content']['filling']) && $block_data['content']['filling'] == 'products') {
            $block_data['properties']['filling']['products']['rss_sort_by'] = empty($block_data['properties']['filling']['products']['rss_sort_by']) ? 'U' : $block_data['properties']['filling']['products']['rss_sort_by'];

            $_params = array(
                'sort_by' => ($block_data['properties']['filling']['products']['rss_sort_by'] == 'U') ? 'updated_timestamp' : 'timestamp',
                'sort_order' => 'desc'
            );

            $max_items = !empty($block_data['properties']['max_item']) ? $block_data['properties']['max_item'] : Registry::get('settings.Appearance.products_per_page');

            list($products) = fn_get_products($_params, $max_items, $lang_code);
            fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

            $additional_data['title'] = !empty($block_data['properties']['feed_title']) ? $block_data['properties']['feed_title'] : __('products') . '::' . __('page_title', '', $lang_code);
            $additional_data['description'] = !empty($block_data['properties']['feed_description']) ? $block_data['properties']['feed_description'] : $additional_data['title'];
            $additional_data['link'] = fn_url('', 'C', 'http', $lang_code);
            $additional_data['language'] = $lang_code;
            $additional_data['lastBuildDate'] = !empty($products[0]['updated_timestamp']) ? $products[0]['updated_timestamp'] : TIME;

            $items_data = fn_format_products_items($products, $block_data['properties']['filling']['products'], $lang_code);
        }
    } elseif (!empty($params['category_id'])) {
        //show rss feed for categories page
        list($items_data, $additional_data) = fn_format_categories_items($params, $lang_code);
    }

    /**
     * Executes after feed items and properties were fetched from DB and all data post-processing was done
     *
     * @param array $items_data      Feed items
     * @param array $additional_data Feed properties (title, description, etc.)
     * @param array $block_data      Block settings
     * @param string $lang_code      Two-letter language code
     */
    fn_set_hook('generate_rss_feed', $items_data, $additional_data, $block_data, $lang_code);

    return array($items_data, $additional_data);
}