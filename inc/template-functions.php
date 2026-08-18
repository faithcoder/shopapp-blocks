<?php
/**
 * Template helpers for ShopApp.
 *
 * @package ShopAppBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks whether WooCommerce is active.
 *
 * @return bool
 */
function shopapp_has_woocommerce() {
	return class_exists( 'WooCommerce' ) && function_exists( 'wc_get_products' );
}

/**
 * Formats a price for WooCommerce or demo output.
 *
 * @param float|string $price Product price.
 * @return string
 */
function shopapp_format_price( $price ) {
	if ( function_exists( 'wc_price' ) ) {
		return wp_strip_all_tags( wc_price( $price ) );
	}

	return '$' . number_format_i18n( (float) $price, 0 );
}

/**
 * Formats card price markup with a separately stylable currency symbol.
 *
 * @param float|string $price Product price.
 * @return string
 */
function shopapp_format_card_price( $price ) {
	if ( function_exists( 'wc_price' ) ) {
		return wc_price( $price );
	}

	return '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>' . esc_html( number_format_i18n( (float) $price, 0 ) ) . '</span>';
}

/**
 * Formats a product price with sale markup.
 *
 * @param float|string      $price Product price.
 * @param float|string|null $regular_price Regular price.
 * @param bool              $on_sale Whether product is on sale.
 * @return string
 */
function shopapp_format_product_price_html( $price, $regular_price = null, $on_sale = false ) {
	if ( $on_sale && null !== $regular_price && '' !== (string) $regular_price && (float) $regular_price > (float) $price ) {
		return '<span class="shopapp-sale-price"><del>' . wp_kses_post( shopapp_format_card_price( $regular_price ) ) . '</del><ins>' . wp_kses_post( shopapp_format_card_price( $price ) ) . '</ins></span>';
	}

	return '<span class="shopapp-single-price">' . wp_kses_post( shopapp_format_card_price( $price ) ) . '</span>';
}

/**
 * Returns an inline SVG icon.
 *
 * @param string $name Icon name.
 * @return string
 */
function shopapp_icon( $name ) {
	$icons = array(
		'bag'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8V7a5 5 0 0 1 10 0v1"/><path d="M5 8h14l-1 12H6L5 8Z"/><path d="M9 12a3 3 0 0 0 6 0"/></svg>',
		'bell'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18H9"/><path d="M18 10a6 6 0 1 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
		'card'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>',
		'check'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>',
		'heart'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>',
		'home'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>',
		'minus'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/></svg>',
		'plus'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>',
		'rotate'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v7h7"/></svg>',
		'search'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>',
		'shield'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-5"/></svg>',
		'star'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.2L5.8 21 7 14.2 2 9.3l6.9-1L12 2Z"/></svg>',
		'trash'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>',
		'truck'     => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h11v11H3z"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg>',
		'user'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>',
		'zap'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13 2 4 14h7l-1 8 10-13h-7l0-7Z"/></svg>',
	);

	return $icons[ $name ] ?? '';
}

/**
 * Returns demo products used when WooCommerce is unavailable or empty.
 *
 * @return array<int,array<string,mixed>>
 */
function shopapp_get_demo_products() {
	$plugin_uri = SHOPAPP_BLOCKS_URL;

	return array(
		array(
			'id'          => 'demo-aura',
			'name'        => __( 'Aura Over-Ear', 'shopapp-blocks' ),
			'tagline'     => __( 'Warm sound, soft leather, travel ready', 'shopapp-blocks' ),
			'price'       => 289,
			'price_html'  => shopapp_format_product_price_html( 289 ),
			'price_text'  => shopapp_format_price( 289 ),
			'image'       => $plugin_uri . 'assets/images/demo-aura.svg',
			'category'    => __( 'Audio', 'shopapp-blocks' ),
			'rating'      => '4.8',
			'badge'       => __( 'New', 'shopapp-blocks' ),
		'colors'      => array( __( 'Black', 'shopapp-blocks' ), __( 'Sand', 'shopapp-blocks' ) ),
		'checkoutUrl' => home_url( '/' ),
		'on_sale'     => false,
		),
		array(
			'id'          => 'demo-meridian',
			'name'        => __( 'Meridian Watch', 'shopapp-blocks' ),
			'tagline'     => __( 'Cognac strap, sapphire glass', 'shopapp-blocks' ),
			'price'       => 420,
			'price_html'  => shopapp_format_product_price_html( 420 ),
			'price_text'  => shopapp_format_price( 420 ),
			'image'       => $plugin_uri . 'assets/images/demo-watch.svg',
			'category'    => __( 'Time', 'shopapp-blocks' ),
			'rating'      => '4.9',
			'badge'       => '',
		'colors'      => array( __( 'Cognac', 'shopapp-blocks' ), __( 'Steel', 'shopapp-blocks' ) ),
		'checkoutUrl' => home_url( '/' ),
		'on_sale'     => false,
		),
		array(
			'id'          => 'demo-range',
			'name'        => __( 'Range Weekender', 'shopapp-blocks' ),
			'tagline'     => __( 'Waxed canvas, 38L, lifetime repairs', 'shopapp-blocks' ),
			'price'       => 245,
			'price_html'  => shopapp_format_product_price_html( 245 ),
			'price_text'  => shopapp_format_price( 245 ),
			'image'       => $plugin_uri . 'assets/images/demo-bag.svg',
			'category'    => __( 'Carry', 'shopapp-blocks' ),
			'rating'      => '4.7',
			'badge'       => __( 'Bestseller', 'shopapp-blocks' ),
		'colors'      => array( __( 'Olive', 'shopapp-blocks' ), __( 'Charcoal', 'shopapp-blocks' ) ),
		'checkoutUrl' => home_url( '/' ),
		'on_sale'     => false,
		),
		array(
			'id'          => 'demo-kiln',
			'name'        => __( 'Kiln Pour-Over', 'shopapp-blocks' ),
			'tagline'     => __( 'Terracotta ceramic, slow mornings', 'shopapp-blocks' ),
			'price'       => 96,
			'price_html'  => shopapp_format_product_price_html( 96 ),
			'price_text'  => shopapp_format_price( 96 ),
			'image'       => $plugin_uri . 'assets/images/demo-pour.svg',
			'category'    => __( 'Home', 'shopapp-blocks' ),
			'rating'      => '4.6',
			'badge'       => '',
		'colors'      => array( __( 'Terracotta', 'shopapp-blocks' ), __( 'Cream', 'shopapp-blocks' ) ),
		'checkoutUrl' => home_url( '/' ),
		'on_sale'     => false,
		),
	);
}

/**
 * Converts a WooCommerce product into card data.
 *
 * @param WC_Product $product Product object.
 * @return array<string,mixed>
 */
function shopapp_get_product_data( $product ) {
	$product_id = $product->get_id();
	$image_id   = $product->get_image_id();
	$image      = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$terms      = get_the_terms( $product_id, 'product_cat' );
	$category   = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0]->name : __( 'Shop', 'shopapp-blocks' );

	$price         = $product->get_price();
	$regular_price = $product->get_regular_price();
	$on_sale       = $product->is_on_sale();

	return array(
		'id'          => $product_id,
		'name'        => $product->get_name(),
		'tagline'     => wp_strip_all_tags( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ),
		'price'       => (float) $price,
		'price_html'  => shopapp_format_product_price_html( $price, $regular_price, $on_sale ),
		'price_text'  => wp_strip_all_tags( shopapp_format_price( $price ) ),
		'image'       => $image ? $image : SHOPAPP_BLOCKS_URL . 'assets/images/demo-pour.svg',
		'category'    => $category,
		'rating'      => $product->get_average_rating() ? number_format_i18n( (float) $product->get_average_rating(), 1 ) : '4.8',
		'badge'       => $product->is_featured() ? __( 'Bestseller', 'shopapp-blocks' ) : '',
		'colors'      => shopapp_get_product_color_labels( $product ),
		'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
		'permalink'   => $product->get_permalink(),
		'type'        => $product->get_type(),
		'on_sale'     => $on_sale,
	);
}

/**
 * Adds a sanitized hex color CSS variable declaration.
 *
 * @param string $css CSS declarations.
 * @param string $property CSS custom property.
 * @param string $value Color value.
 * @return string
 */
function shopapp_add_color_var( $css, $property, $value ) {
	$color = sanitize_hex_color( $value );

	if ( $color ) {
		$css .= $property . ':' . $color . ';';
	}

	return $css;
}

/**
 * Builds Product Grid style variables from block attributes.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function shopapp_get_product_grid_style( $attributes ) {
	$style  = '--shopapp-grid-columns:' . max( 1, min( 12, (int) $attributes['columns'] ) ) . ';';
	$style .= '--shopapp-tablet-columns:' . max( 1, min( 8, (int) $attributes['tabletColumns'] ) ) . ';';
	$style .= '--shopapp-mobile-columns:' . max( 1, min( 4, (int) $attributes['mobileColumns'] ) ) . ';';
	$style .= '--shopapp-card-radius:' . max( 0, min( 80, (int) $attributes['cardRadius'] ) ) . 'px;';

	$style = shopapp_add_color_var( $style, '--shopapp-card-info-bg', $attributes['infoBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-card-info-color', $attributes['infoColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-rating-star-color', $attributes['ratingStarColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-rating-text-color', $attributes['ratingTextColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-category-color', $attributes['categoryColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-sale-bg', $attributes['onSaleBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-sale-color', $attributes['onSaleColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-title-color', $attributes['titleColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-price-color', $attributes['priceColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-currency-color', $attributes['currencyColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-add-bg', $attributes['addButtonBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-add-color', $attributes['addButtonColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-load-more-bg', $attributes['loadMoreBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-load-more-color', $attributes['loadMoreColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-pagination-bg', $attributes['paginationBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-pagination-color', $attributes['paginationColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-pagination-active-bg', $attributes['paginationActiveBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-pagination-active-color', $attributes['paginationActiveColor'] );

	return $style;
}

/**
 * Gets product color labels from attributes.
 *
 * @param WC_Product $product Product object.
 * @return string[]
 */
function shopapp_get_product_color_labels( $product ) {
	$colors     = array();
	$attributes = $product->get_attributes();

	foreach ( $attributes as $attribute ) {
		$name = wc_attribute_label( $attribute->get_name() );
		if ( false === stripos( $name, 'color' ) && false === stripos( $name, 'colour' ) ) {
			continue;
		}

		if ( $attribute->is_taxonomy() ) {
			$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
			$colors = array_merge( $colors, $terms );
		} else {
			$colors = array_merge( $colors, $attribute->get_options() );
		}
	}

	$colors = array_filter( array_map( 'sanitize_text_field', $colors ) );

	return ! empty( $colors ) ? array_values( array_slice( $colors, 0, 3 ) ) : array( __( 'Standard', 'shopapp-blocks' ) );
}

/**
 * Gets product category options for the block editor.
 *
 * @return array<int,array<string,string>>
 */
function shopapp_get_product_category_options() {
	$options = array(
		array(
			'label' => __( 'All categories', 'shopapp-blocks' ),
			'value' => '',
		),
	);

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $options;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $options;
	}

	foreach ( $terms as $term ) {
		$options[] = array(
			'label' => $term->name,
			'value' => $term->slug,
		);
	}

	return $options;
}

/**
 * Queries products for the storefront block.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return array<int,array<string,mixed>>
 */
function shopapp_get_storefront_products( $attributes ) {
	if ( ! shopapp_has_woocommerce() ) {
		return shopapp_get_demo_products();
	}

	$orderby = sanitize_key( $attributes['orderby'] ?? 'date' );
	$per_page = max( 1, (int) ( $attributes['perPage'] ?? 12 ) );
	$page     = max( 1, (int) ( $attributes['page'] ?? 1 ) );
	$args     = array(
		'limit'  => $per_page,
		'page'   => $page,
		'status' => 'publish',
		'order'  => 'DESC',
	);

	if ( ! empty( $attributes['category'] ) ) {
		$args['category'] = array( sanitize_title( $attributes['category'] ) );
	}

	switch ( $orderby ) {
		case 'name':
			$args['orderby'] = 'name';
			$args['order']   = 'ASC';
			break;
		case 'price':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = '_price';
			$args['order']   = 'ASC';
			break;
		case 'rating':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = '_wc_average_rating';
			break;
		case 'popularity':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = 'total_sales';
			break;
		case 'date':
		default:
			$args['orderby'] = 'date';
			break;
	}

	$products = wc_get_products( $args );

	if ( empty( $products ) ) {
		return array();
	}

	return array_map( 'shopapp_get_product_data', $products );
}

/**
 * Counts products for a storefront query.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return int
 */
function shopapp_count_storefront_products( $attributes ) {
	if ( ! shopapp_has_woocommerce() ) {
		return count( shopapp_get_demo_products() );
	}

	$args = array(
		'status' => 'publish',
		'limit'  => -1,
		'return' => 'ids',
	);

	if ( ! empty( $attributes['category'] ) ) {
		$args['category'] = array( sanitize_title( $attributes['category'] ) );
	}

	return count( wc_get_products( $args ) );
}

/**
 * Registers public query vars used by ShopApp blocks.
 *
 * @param string[] $vars Query variable names.
 * @return string[]
 */
function shopapp_register_query_vars( $vars ) {
	$vars[] = 'product-page';

	return $vars;
}
add_filter( 'query_vars', 'shopapp_register_query_vars' );

/**
 * Gets the base URL for Product Grid numbered pagination.
 *
 * @return string
 */
function shopapp_get_product_grid_base_url() {
	$queried_object_id = get_queried_object_id();

	if ( $queried_object_id && ( is_singular() || is_front_page() || is_home() ) ) {
		return get_permalink( $queried_object_id );
	}

	return remove_query_arg( 'product-page' );
}

/**
 * Gets a Product Grid page URL.
 *
 * @param int $page Page number.
 * @return string
 */
function shopapp_get_product_grid_page_url( $page ) {
	$base_url = shopapp_get_product_grid_base_url();

	if ( 1 === (int) $page ) {
		return remove_query_arg( 'product-page', $base_url );
	}

	return add_query_arg( 'product-page', (int) $page, $base_url );
}

/**
 * Gets default checkout-bar menu items.
 *
 * @return array<int,array<string,mixed>>
 */
function shopapp_get_default_checkout_menu_items() {
	return array(
		array(
			'label'   => __( 'Shop', 'shopapp-blocks' ),
			'icon'    => 'home',
			'link'    => '',
			'popup'   => 'shop',
			'visible' => true,
			'active'  => true,
		),
		array(
			'label'   => __( 'Search', 'shopapp-blocks' ),
			'icon'    => 'search',
			'link'    => '',
			'popup'   => 'search',
			'visible' => true,
			'active'  => false,
		),
		array(
			'label'   => __( 'Saved', 'shopapp-blocks' ),
			'icon'    => 'heart',
			'link'    => '',
			'popup'   => 'saved',
			'visible' => true,
			'active'  => false,
		),
		array(
			'label'   => __( 'You', 'shopapp-blocks' ),
			'icon'    => 'user',
			'link'    => '',
			'popup'   => 'you',
			'visible' => true,
			'active'  => false,
		),
	);
}

/**
 * Sanitizes checkout-bar menu items.
 *
 * @param array<mixed> $items Menu item data.
 * @return array<int,array<string,mixed>>
 */
function shopapp_sanitize_checkout_menu_items( $items ) {
	$allowed_icons = array( 'bag', 'bell', 'card', 'check', 'heart', 'home', 'search', 'shield', 'star', 'truck', 'user', 'zap' );
	$allowed_popups = array( 'shop', 'search', 'saved', 'you' );
	$items = is_array( $items ) && ! empty( $items ) ? $items : shopapp_get_default_checkout_menu_items();
	$clean = array();

	foreach ( $items as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}

		$icon = isset( $item['icon'] ) ? sanitize_key( $item['icon'] ) : 'home';
		$popup = isset( $item['popup'] ) ? sanitize_key( $item['popup'] ) : '';

		$clean[] = array(
			'label'   => isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '',
			'icon'    => in_array( $icon, $allowed_icons, true ) ? $icon : 'home',
			'link'    => isset( $item['link'] ) ? esc_url_raw( $item['link'] ) : '',
			'popup'   => in_array( $popup, $allowed_popups, true ) ? $popup : '',
			'visible' => ! isset( $item['visible'] ) || (bool) $item['visible'],
			'active'  => ! empty( $item['active'] ),
		);
	}

	return $clean;
}

/**
 * Builds checkout-bar inline style variables.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function shopapp_get_checkout_bar_style( $attributes ) {
	$opacity = max( 0, min( 100, (int) $attributes['opacity'] ) ) / 100;
	$style  = '--shopapp-checkout-width:' . max( 240, min( 1400, (int) $attributes['width'] ) ) . 'px;';
	$style .= '--shopapp-checkout-height:' . max( 56, min( 220, (int) $attributes['height'] ) ) . 'px;';
	$style .= '--shopapp-checkout-padding:' . max( 0, min( 48, (int) $attributes['padding'] ) ) . 'px;';
	$style .= '--shopapp-checkout-gap:' . max( 0, min( 40, (int) $attributes['gap'] ) ) . 'px;';
	$style .= '--shopapp-checkout-radius:' . max( 0, min( 120, (int) $attributes['borderRadius'] ) ) . 'px;';
	$style .= '--shopapp-checkout-border-width:' . max( 0, min( 12, (int) $attributes['borderWidth'] ) ) . 'px;';
	$style .= '--shopapp-checkout-blur:' . max( 0, min( 60, (int) $attributes['blur'] ) ) . 'px;';
	$style .= '--shopapp-checkout-saturate:' . max( 0, min( 300, (int) $attributes['saturate'] ) ) . '%;';
	$style .= '--shopapp-checkout-opacity:' . $opacity . ';';
	$style .= '--shopapp-checkout-z:' . max( 1, min( 9999, (int) $attributes['zIndex'] ) ) . ';';
	$style .= '--shopapp-checkout-icon-size:' . max( 12, min( 80, (int) $attributes['iconSize'] ) ) . 'px;';
	$style .= '--shopapp-checkout-label-size:' . max( 10, min( 32, (int) $attributes['labelSize'] ) ) . 'px;';
	$style .= '--shopapp-checkout-label-weight:' . max( 100, min( 900, (int) $attributes['labelWeight'] ) ) . ';';
	$style .= '--shopapp-checkout-cart-size:' . max( 48, min( 160, (int) $attributes['cartSize'] ) ) . 'px;';
	$style .= '--shopapp-checkout-cart-icon-size:' . max( 18, min( 96, (int) $attributes['cartIconSize'] ) ) . 'px;';
	$style .= '--shopapp-checkout-cart-offset-y:' . max( -140, min( 80, (int) $attributes['cartOffsetY'] ) ) . 'px;';

	$style = shopapp_add_color_var( $style, '--shopapp-checkout-bg', $attributes['backgroundColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-color', $attributes['textColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-border-color', $attributes['borderColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-icon-color', $attributes['iconColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-label-color', $attributes['labelColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-active-color', $attributes['activeColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-cart-bg', $attributes['cartBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-cart-color', $attributes['cartColor'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-count-bg', $attributes['countBackground'] );
	$style = shopapp_add_color_var( $style, '--shopapp-checkout-count-color', $attributes['countColor'] );

	return $style;
}

/**
 * Renders the storefront block.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function shopapp_render_storefront( $attributes = array() ) {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'sectionTitle'   => __( 'Curated for you', 'shopapp-blocks' ),
			'category'       => '',
			'orderby'        => 'date',
			'perPage'        => 12,
			'columns'        => 4,
			'tabletColumns'  => 2,
			'mobileColumns'  => 2,
			'pagination'     => 'load-more',
			'showRatings'    => true,
			'showCategory'   => true,
			'showOnSale'     => true,
			'cardRadius'     => 34,
			'infoBackground' => '',
			'infoColor'      => '',
			'ratingStarColor' => '',
			'ratingTextColor' => '',
			'categoryColor'  => '',
			'onSaleBackground' => '',
			'onSaleColor'    => '',
			'titleColor'     => '',
			'priceColor'     => '',
			'currencyColor'  => '',
			'addButtonBackground' => '',
			'addButtonColor' => '',
			'loadMoreBackground' => '',
			'loadMoreColor'  => '',
			'paginationBackground' => '',
			'paginationColor' => '',
			'paginationActiveBackground' => '',
			'paginationActiveColor' => '',
		)
	);

	$total        = shopapp_count_storefront_products( $attributes );
	$max_pages    = (int) ceil( $total / max( 1, (int) $attributes['perPage'] ) );
	$current_page = 1;

	if ( 'pagination' === $attributes['pagination'] ) {
		$current_page = absint( get_query_var( 'product-page', 1 ) );
		$current_page = max( 1, $current_page );
		$current_page = $max_pages ? min( $current_page, $max_pages ) : 1;
	}

	$attributes['page'] = $current_page;
	$products           = shopapp_get_storefront_products( $attributes );
	$wrapper_attrs      = get_block_wrapper_attributes(
		array(
			'class' => 'shopapp-storefront shopapp-product-grid-block alignfull',
			'style' => shopapp_get_product_grid_style( $attributes ),
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-shopapp-query="<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>" data-shopapp-page="<?php echo esc_attr( $current_page ); ?>" data-shopapp-max-pages="<?php echo esc_attr( $max_pages ); ?>">
		<div class="shopapp-app-shell">
			<div class="shopapp-section-heading" id="shopapp-products">
				<h2><?php echo esc_html( $attributes['sectionTitle'] ); ?></h2>
				<span><?php echo esc_html( sprintf( _n( '%s item', '%s items', $total, 'shopapp-blocks' ), number_format_i18n( $total ) ) ); ?></span>
			</div>
			<div class="shopapp-product-grid" data-shopapp-product-grid>
			<?php if ( ! empty( $products ) ) : ?>
				<?php foreach ( $products as $product ) : ?>
					<?php shopapp_render_product_card( $product, $attributes ); ?>
				<?php endforeach; ?>
			<?php else : ?>
				<p class="shopapp-products-empty"><?php esc_html_e( 'No products found for this query.', 'shopapp-blocks' ); ?></p>
			<?php endif; ?>
			</div>
			<?php if ( $max_pages > 1 && 'load-more' === $attributes['pagination'] ) : ?>
				<button class="shopapp-load-more shopapp-button shopapp-button--soft" type="button" data-shopapp-load-more><?php esc_html_e( 'Load more', 'shopapp-blocks' ); ?></button>
			<?php elseif ( $max_pages > 1 && 'pagination' === $attributes['pagination'] ) : ?>
				<nav class="shopapp-pagination" aria-label="<?php esc_attr_e( 'Product pagination', 'shopapp-blocks' ); ?>">
				<?php for ( $page = 1; $page <= $max_pages; $page++ ) : ?>
					<a href="<?php echo esc_url( shopapp_get_product_grid_page_url( $page ) ); ?>"<?php echo $current_page === $page ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo esc_html( number_format_i18n( $page ) ); ?></a>
				<?php endfor; ?>
				</nav>
			<?php endif; ?>
		</div>
		<?php shopapp_render_product_sheet(); ?>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Renders a product card.
 *
 * @param array<string,mixed> $product Product data.
 * @return void
 */
function shopapp_render_product_card( $product, $attributes = array() ) {
	$product_json = wp_json_encode( $product );
	$show_ratings = ! isset( $attributes['showRatings'] ) || (bool) $attributes['showRatings'];
	$show_category = ! isset( $attributes['showCategory'] ) || (bool) $attributes['showCategory'];
	$show_on_sale = ! isset( $attributes['showOnSale'] ) || (bool) $attributes['showOnSale'];
	?>
	<article class="shopapp-product-card" data-shopapp-category="<?php echo esc_attr( sanitize_title( $product['category'] ) ); ?>" data-shopapp-product="<?php echo esc_attr( $product_json ); ?>">
		<button class="shopapp-product-card__media" type="button" data-shopapp-open-product>
			<img src="<?php echo esc_url( $product['image'] ); ?>" alt="<?php echo esc_attr( $product['name'] ); ?>" loading="lazy">
			<?php if ( $show_on_sale && ! empty( $product['on_sale'] ) ) : ?>
				<span class="shopapp-badge shopapp-badge--sale"><?php esc_html_e( 'On Sale', 'shopapp-blocks' ); ?></span>
			<?php endif; ?>
			<?php if ( ! empty( $product['badge'] ) ) : ?>
				<span class="shopapp-badge"><?php echo esc_html( $product['badge'] ); ?></span>
			<?php endif; ?>
		</button>
		<?php if ( is_numeric( $product['id'] ) ) : ?>
			<button class="shopapp-card-save" type="button" data-shopapp-save data-product-id="<?php echo esc_attr( $product['id'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Save %s', 'shopapp-blocks' ), $product['name'] ) ); ?>" aria-pressed="false"><?php echo shopapp_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		<?php endif; ?>
		<div class="shopapp-product-card__body">
			<?php if ( $show_ratings || $show_category ) : ?>
				<p class="shopapp-rating">
					<?php if ( $show_ratings ) : ?>
						<span class="shopapp-rating__score"><?php echo shopapp_icon( 'star' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <span><?php echo esc_html( $product['rating'] ); ?></span></span>
					<?php endif; ?>
					<?php if ( $show_ratings && $show_category ) : ?>
						<span class="shopapp-rating__sep">&middot;</span>
					<?php endif; ?>
					<?php if ( $show_category ) : ?>
						<span class="shopapp-card-category"><?php echo esc_html( $product['category'] ); ?></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<h3><button type="button" data-shopapp-open-product><?php echo esc_html( $product['name'] ); ?></button></h3>
			<p class="shopapp-price"><?php echo wp_kses_post( $product['price_html'] ); ?></p>
			<button class="shopapp-card-add" type="button" data-shopapp-add data-product-id="<?php echo esc_attr( $product['id'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Add %s to bag', 'shopapp-blocks' ), $product['name'] ) ); ?>"><?php echo shopapp_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
		</div>
	</article>
	<?php
}

/**
 * Renders the search and category filter block.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function shopapp_render_search_filters( $attributes = array() ) {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'brand'       => __( 'Northbound', 'shopapp-blocks' ),
			'greeting'    => __( 'Good morning, Alex', 'shopapp-blocks' ),
			'placeholder' => __( 'Search essentials', 'shopapp-blocks' ),
			'showHeader'  => true,
			'showSearch'  => true,
			'showFilters' => true,
		)
	);
	$options = shopapp_get_product_category_options();
	$wrapper_attrs = get_block_wrapper_attributes( array( 'class' => 'shopapp-storefront shopapp-search-filter-block alignfull' ) );

	ob_start();
	?>
	<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="shopapp-app-shell shopapp-app-shell--compact">
			<?php if ( $attributes['showHeader'] ) : ?>
				<header class="shopapp-app-header">
					<div>
						<p class="shopapp-brand"><?php echo esc_html( $attributes['brand'] ); ?></p>
						<h1><?php echo esc_html( $attributes['greeting'] ); ?></h1>
					</div>
					<button class="shopapp-icon-button" type="button" aria-label="<?php esc_attr_e( 'Notifications', 'shopapp-blocks' ); ?>"><?php echo shopapp_icon( 'bell' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				</header>
			<?php endif; ?>
			<?php if ( $attributes['showSearch'] ) : ?>
				<form class="shopapp-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php echo shopapp_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<label class="screen-reader-text" for="shopapp-product-search"><?php esc_html_e( 'Search products', 'shopapp-blocks' ); ?></label>
					<input id="shopapp-product-search" type="search" name="s" placeholder="<?php echo esc_attr( $attributes['placeholder'] ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
					<input type="hidden" name="post_type" value="product">
				</form>
			<?php endif; ?>
			<?php if ( $attributes['showFilters'] ) : ?>
				<div class="shopapp-filter-list" aria-label="<?php esc_attr_e( 'Product filters', 'shopapp-blocks' ); ?>">
					<a class="shopapp-filter is-active" href="<?php echo esc_url( remove_query_arg( 'product_cat' ) ); ?>"><?php esc_html_e( 'All', 'shopapp-blocks' ); ?></a>
					<?php foreach ( $options as $option ) : ?>
						<?php if ( '' === $option['value'] ) { continue; } ?>
						<a class="shopapp-filter" href="<?php echo esc_url( add_query_arg( 'product_cat', $option['value'], home_url( '/' ) ) ); ?>"><?php echo esc_html( $option['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Renders the bottom navigation block.
 *
 * @param array<string,mixed> $attributes Block attributes.
 * @return string
 */
function shopapp_render_bottom_nav( $attributes = array() ) {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'showLabels'       => true,
			'showCart'         => true,
			'menuItems'        => array(),
			'width'            => 660,
			'height'           => 88,
			'padding'          => 10,
			'gap'              => 8,
			'borderRadius'     => 38,
			'borderWidth'      => 1,
			'blur'             => 20,
			'saturate'         => 160,
			'opacity'          => 94,
			'zIndex'           => 30,
			'iconSize'         => 31,
			'labelSize'        => 14,
			'labelWeight'      => 800,
			'cartSize'         => 88,
			'cartIconSize'     => 39,
			'cartOffsetY'      => -50,
			'enableBuzz'       => true,
			'backgroundColor'  => '',
			'textColor'        => '',
			'borderColor'      => '',
			'iconColor'        => '',
			'labelColor'       => '',
			'activeColor'      => '',
			'cartBackground'   => '',
			'cartColor'        => '',
			'countBackground'  => '',
			'countColor'       => '',
		)
	);
	$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' );
	$menu_items   = shopapp_sanitize_checkout_menu_items( $attributes['menuItems'] );
	$menu_items   = array_values(
		array_filter(
			$menu_items,
			static function ( $item ) {
				return ! empty( $item['visible'] );
			}
		)
	);
	$item_count   = count( $menu_items ) + ( $attributes['showCart'] ? 1 : 0 );
	$split_index  = (int) ceil( count( $menu_items ) / 2 );
	$classes      = 'shopapp-storefront shopapp-bottom-nav-block shopapp-checkout-block alignfull';
	$wrapper_attrs = get_block_wrapper_attributes(
		array(
			'class' => $classes,
			'style' => shopapp_get_checkout_bar_style( $attributes ),
		)
	);

	ob_start();
	?>
	<section <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<nav class="shopapp-bottom-nav"<?php echo $attributes['enableBuzz'] ? ' data-shopapp-buzz="true"' : ''; ?> style="<?php echo esc_attr( '--shopapp-checkout-item-count:' . max( 1, $item_count ) . ';' ); ?>" aria-label="<?php esc_attr_e( 'Shop checkout navigation', 'shopapp-blocks' ); ?>">
			<?php foreach ( array_slice( $menu_items, 0, $split_index ) as $menu_item ) : ?>
				<?php shopapp_render_checkout_menu_item( $menu_item, (bool) $attributes['showLabels'] ); ?>
			<?php endforeach; ?>
			<?php if ( $attributes['showCart'] ) : ?>
				<button class="shopapp-bag-trigger" type="button" data-shopapp-open-checkout aria-label="<?php esc_attr_e( 'Open bag', 'shopapp-blocks' ); ?>"><?php echo shopapp_icon( 'bag' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span class="shopapp-bag-count">0</span></button>
			<?php endif; ?>
			<?php foreach ( array_slice( $menu_items, $split_index ) as $menu_item ) : ?>
				<?php shopapp_render_checkout_menu_item( $menu_item, (bool) $attributes['showLabels'] ); ?>
			<?php endforeach; ?>
		</nav>
		<?php shopapp_render_quick_checkout( $checkout_url ); ?>
		<?php shopapp_render_nav_popup(); ?>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Renders one checkout-bar menu item.
 *
 * @param array<string,mixed> $item Menu item.
 * @param bool                $show_label Whether to show labels.
 * @return void
 */
function shopapp_render_checkout_menu_item( $item, $show_label ) {
	$label = '' !== $item['label'] ? $item['label'] : __( 'Menu item', 'shopapp-blocks' );
	$classes = ! empty( $item['active'] ) ? ' class="is-active"' : '';
	$label_markup = $show_label ? '<span>' . esc_html( $label ) . '</span>' : '';

	if ( ! empty( $item['link'] ) ) {
		printf(
			'<a%1$s href="%2$s" aria-label="%3$s">%4$s%5$s</a>',
			$classes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_url( $item['link'] ),
			esc_attr( $label ),
			shopapp_icon( $item['icon'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$label_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
		return;
	}

	printf(
		'<button%1$s type="button" data-shopapp-nav-popup="%2$s" aria-label="%3$s">%4$s%5$s</button>',
		$classes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_attr( $item['popup'] ? $item['popup'] : 'shop' ),
		esc_attr( $label ),
		shopapp_icon( $item['icon'] ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$label_markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Renders product detail sheet.
 */
function shopapp_render_product_sheet() {
	?>
	<div class="shopapp-sheet" data-shopapp-product-sheet hidden>
		<div class="shopapp-sheet__overlay" data-shopapp-close-sheet></div>
		<div class="shopapp-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="shopapp-product-title">
			<div class="shopapp-sheet__handle"></div>
			<img class="shopapp-sheet__image" src="" alt="">
			<div class="shopapp-sheet__title-row">
				<div>
					<h2 id="shopapp-product-title"></h2>
					<p class="shopapp-sheet__tagline"></p>
				</div>
				<p class="shopapp-sheet__price"></p>
			</div>
			<p class="shopapp-sheet__rating shopapp-rating"></p>
			<div class="shopapp-sheet__colors"></div>
			<ul class="shopapp-benefits">
				<li><?php echo shopapp_icon( 'truck' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Free 2-day', 'shopapp-blocks' ); ?></span></li>
				<li><?php echo shopapp_icon( 'rotate' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( '60-day returns', 'shopapp-blocks' ); ?></span></li>
				<li><?php echo shopapp_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( '2-yr warranty', 'shopapp-blocks' ); ?></span></li>
			</ul>
			<div class="shopapp-sheet__actions">
				<button class="shopapp-button shopapp-button--soft" type="button" data-shopapp-sheet-add><?php esc_html_e( 'Add to bag', 'shopapp-blocks' ); ?></button>
				<button class="shopapp-button shopapp-button--ember" type="button" data-shopapp-sheet-buy><?php esc_html_e( 'Buy now', 'shopapp-blocks' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Renders quick checkout drawer.
 *
 * @param string $checkout_url WooCommerce checkout URL.
 */
function shopapp_render_quick_checkout( $checkout_url ) {
	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
	?>
	<div class="shopapp-checkout" data-shopapp-checkout hidden>
		<div class="shopapp-checkout__overlay" data-shopapp-close-checkout></div>
		<div class="shopapp-checkout__panel" role="dialog" aria-modal="true" aria-labelledby="shopapp-checkout-title">
			<div class="shopapp-sheet__handle"></div>
			<div data-shopapp-checkout-review>
				<h2 id="shopapp-checkout-title"><?php esc_html_e( 'Quick checkout', 'shopapp-blocks' ); ?></h2>
				<p class="shopapp-checkout__intro"><?php esc_html_e( 'Review your bag before secure checkout.', 'shopapp-blocks' ); ?></p>
				<ul class="shopapp-checkout__lines" data-shopapp-lines></ul>
				<form class="shopapp-checkout__coupon" data-shopapp-coupon-form>
					<label class="screen-reader-text" for="shopapp-coupon-code"><?php esc_html_e( 'Coupon code', 'shopapp-blocks' ); ?></label>
					<input id="shopapp-coupon-code" type="text" name="coupon" placeholder="<?php esc_attr_e( 'Coupon code', 'shopapp-blocks' ); ?>">
					<button type="submit"><?php esc_html_e( 'Apply', 'shopapp-blocks' ); ?></button>
					<p data-shopapp-coupon-status aria-live="polite"></p>
				</form>
				<dl class="shopapp-checkout__totals">
					<div><dt><?php esc_html_e( 'Subtotal', 'shopapp-blocks' ); ?></dt><dd data-shopapp-subtotal>$0</dd></div>
					<div data-shopapp-discount-row hidden><dt><?php esc_html_e( 'Discount', 'shopapp-blocks' ); ?></dt><dd data-shopapp-discount></dd></div>
					<div><dt><?php esc_html_e( 'Shipping', 'shopapp-blocks' ); ?></dt><dd data-shopapp-shipping><?php esc_html_e( 'Calculated at checkout', 'shopapp-blocks' ); ?></dd></div>
					<div data-shopapp-tax-row hidden><dt><?php esc_html_e( 'Tax', 'shopapp-blocks' ); ?></dt><dd data-shopapp-tax></dd></div>
					<div class="shopapp-total"><dt><?php esc_html_e( 'Total', 'shopapp-blocks' ); ?></dt><dd data-shopapp-total>$0</dd></div>
				</dl>
				<div class="shopapp-checkout__actions">
					<a class="shopapp-button shopapp-button--ember shopapp-checkout__pay" data-shopapp-checkout-link href="<?php echo esc_url( $checkout_url ); ?>"><?php echo shopapp_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <span><?php esc_html_e( 'Proceed to checkout', 'shopapp-blocks' ); ?></span></a>
					<a class="shopapp-button shopapp-checkout__cart-link" data-shopapp-cart-link href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'View cart', 'shopapp-blocks' ); ?></a>
					<button class="shopapp-checkout__continue" type="button" data-shopapp-continue><?php esc_html_e( 'Continue shopping', 'shopapp-blocks' ); ?></button>
				</div>
				<p class="shopapp-checkout__note"><?php esc_html_e( 'Shipping, taxes, and payment methods are finalized at checkout.', 'shopapp-blocks' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Renders reusable bottom-nav popup.
 */
function shopapp_render_nav_popup() {
	?>
	<div class="shopapp-checkout shopapp-nav-popup" data-shopapp-nav-sheet hidden>
		<div class="shopapp-checkout__overlay" data-shopapp-close-nav></div>
		<div class="shopapp-checkout__panel" role="dialog" aria-modal="true" aria-labelledby="shopapp-nav-title">
			<div class="shopapp-sheet__handle"></div>
			<div class="shopapp-nav-popup__heading">
				<h2 id="shopapp-nav-title" data-shopapp-nav-title><?php esc_html_e( 'Shop', 'shopapp-blocks' ); ?></h2>
				<button type="button" data-shopapp-close-nav aria-label="<?php esc_attr_e( 'Close', 'shopapp-blocks' ); ?>">&times;</button>
			</div>
			<div class="shopapp-nav-popup__content" data-shopapp-nav-content>
				<p><?php esc_html_e( 'Choose a navigation item.', 'shopapp-blocks' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}
