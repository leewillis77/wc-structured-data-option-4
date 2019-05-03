<?php

/**
 * Plugin Name: WooCommerce Variation Structured Data - Option 4
 * Plugin URI: https://github.com/woocommerce/woocommerce/issues/17471
 * Description: Implements alternative structured data for variations as per the discussions on https://github.com/woocommerce/woocommerce/issues/17471.
 * Author: Lee Willis
 * Version: 0.1
 * WC requires at least: 3.2.0
 * WC tested up to: 3.2.0
 * Author URI: http://www.leewillis.co.uk/
 * License: GPLv3
 */

add_filter( 'woocommerce_structured_data_product', function( $markup, $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return $markup;
	}
	// See if we've pre-selected a specific variation with query arguments.
	$data_store   = WC_Data_Store::load( 'product' );
	$variation_id = $data_store->find_matching_product_variation( $product, wp_unslash( $_GET ) );
	$variation    = $variation_id ? wc_get_product( $variation_id ) : false;
	if ( ! empty( $variation ) ) {
		$price_valid_until = date( 'Y-12-31', current_time( 'timestamp', true ) + YEAR_IN_SECONDS );
		if ( $variation->is_on_sale() && $variation->get_date_on_sale_to() ) {
			$price_valid_until = date( 'Y-m-d', $variation->get_date_on_sale_to()->getTimestamp() );
		}
		$markup_offer = $markup['offers'][0];
		unset( $markup_offer['lowPrice'] );
		unset( $markup_offer['highPrice'] );
		unset( $markup_offer['offerCount'] );
		$markup_offer['@type'] = 'Offer';
		$markup_offer['price'] = wc_format_decimal( $variation->get_price(), wc_get_price_decimals() );
		$markup_offer['priceValidUntil'] = $price_valid_until;
		$markup_offer['url'] = $variation->get_permalink();
		$markup['offers'][0] = apply_filters( 'woocommerce_structured_data_product_offer', $markup_offer, $product );
	}
	return $markup;
}, 99, 2);
