<?php

namespace Zhours\Frontend;

defined( 'ABSPATH' ) || exit;

use Zhours\Settings;
use function Zhours\get_current_status;

class Shop
{
    public function __construct()
    {
        add_filter( 'woocommerce_available_payment_gateways', array($this, 'filter_gateways'), 10, 1);
    }

    public function filter_gateways($gateways) {
        $remove_gateways = Settings::getValue('actions', 'gateway functionality', 'remove gateways');
        if (!$remove_gateways) {
            return $gateways;
        }
        return get_current_status() || apply_filters('zh_is_allowed_order_placing', false) ? $gateways : [];
    }

    public static function update_product_grid_item($string, $data, $product) {
        return "<li class=\"wc-block-grid__product\">
            <a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
                {$data->image}
                {$data->title}
            </a>
            {$data->badge}
            {$data->price}
            {$data->rating}
        </li>";
    }
}
