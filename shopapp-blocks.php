<?php
/**
 * Plugin Name:       ShopApp Blocks
 * Description:       Storefront, product grid, and quick checkout blocks for WooCommerce.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Requires Plugins:  woocommerce
 * Author:            M Arif
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       shopapp-blocks
 *
 * @package ShopAppBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHOPAPP_BLOCKS_VERSION', '1.0.0' );
define( 'SHOPAPP_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHOPAPP_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Registers shared block assets.
 */
function shopapp_blocks_register_assets() {
	$editor_file   = SHOPAPP_BLOCKS_DIR . 'assets/js/editor-blocks.js';
	$frontend_file = SHOPAPP_BLOCKS_DIR . 'assets/js/frontend.js';
	$style_file    = SHOPAPP_BLOCKS_DIR . 'assets/css/blocks.css';

	wp_register_script(
		'shopapp-blocks-editor',
		SHOPAPP_BLOCKS_URL . 'assets/js/editor-blocks.js',
		array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-block-editor', 'wp-server-side-render' ),
		file_exists( $editor_file ) ? filemtime( $editor_file ) : SHOPAPP_BLOCKS_VERSION,
		true
	);

	wp_register_script(
		'shopapp-blocks-frontend',
		SHOPAPP_BLOCKS_URL . 'assets/js/frontend.js',
		array(),
		file_exists( $frontend_file ) ? filemtime( $frontend_file ) : SHOPAPP_BLOCKS_VERSION,
		true
	);

	wp_register_style(
		'shopapp-blocks',
		SHOPAPP_BLOCKS_URL . 'assets/css/blocks.css',
		array(),
		file_exists( $style_file ) ? filemtime( $style_file ) : SHOPAPP_BLOCKS_VERSION
	);

	wp_localize_script(
		'shopapp-blocks-frontend',
		'shopappBlocksSettings',
		array(
			'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
			'productsApiUrl'    => esc_url_raw( rest_url( 'wc/store/v1/products' ) ),
			'cartUrl'           => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
			'checkoutUrl'       => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/' ),
			'currency'          => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
			'addToCartNonce'    => wp_create_nonce( 'shopapp_blocks_add_to_cart' ),
			'cartNonce'         => wp_create_nonce( 'shopapp_blocks_cart' ),
			'navigationNonce'   => wp_create_nonce( 'shopapp_blocks_navigation' ),
			'loadProductsNonce' => wp_create_nonce( 'shopapp_blocks_load_products' ),
			'i18n'              => array(
				'addToBag'        => __( 'Add to bag', 'shopapp-blocks' ),
				'addedToSaved'    => __( 'Product saved.', 'shopapp-blocks' ),
				'accountDetails'  => __( 'Account details', 'shopapp-blocks' ),
				'addresses'       => __( 'Addresses', 'shopapp-blocks' ),
				'browseProducts'  => __( 'Browse products', 'shopapp-blocks' ),
				'downloads'       => __( 'Downloads', 'shopapp-blocks' ),
				'loading'         => __( 'Loading...', 'shopapp-blocks' ),
				'loadMore'        => __( 'Load more', 'shopapp-blocks' ),
				'lostPassword'    => __( 'Lost password', 'shopapp-blocks' ),
				'noOrders'        => __( 'No orders yet.', 'shopapp-blocks' ),
				'noResults'       => __( 'No products matched your search.', 'shopapp-blocks' ),
				'noSaved'         => __( 'You have not saved any products yet.', 'shopapp-blocks' ),
				'openAccount'     => __( 'Open My Account', 'shopapp-blocks' ),
				'orders'          => __( 'Orders', 'shopapp-blocks' ),
				'panelError'      => __( 'This panel could not be loaded.', 'shopapp-blocks' ),
				'paymentMethods'  => __( 'Payment methods', 'shopapp-blocks' ),
				'recentOrders'    => __( 'Recent orders', 'shopapp-blocks' ),
				'removeFromSaved' => __( 'Remove from saved', 'shopapp-blocks' ),
				'saveProduct'     => __( 'Save product', 'shopapp-blocks' ),
				'savedHelper'     => __( 'Saved products sync to your account when you sign in.', 'shopapp-blocks' ),
				'search'          => __( 'Search', 'shopapp-blocks' ),
				'searchError'     => __( 'Search could not be loaded.', 'shopapp-blocks' ),
				'searchPrompt'    => __( 'Enter at least two characters to search products.', 'shopapp-blocks' ),
				'searchProducts'  => __( 'Search products', 'shopapp-blocks' ),
				'signIn'          => __( 'Sign in or create account', 'shopapp-blocks' ),
				'signInPrompt'    => __( 'Sign in to view orders, addresses, downloads, and saved products.', 'shopapp-blocks' ),
				'signOut'         => __( 'Sign out', 'shopapp-blocks' ),
				'viewOptions'     => __( 'View options', 'shopapp-blocks' ),
			),
		)
	);
}
add_action( 'init', 'shopapp_blocks_register_assets', 5 );

/**
 * Passes WooCommerce categories to the Product Grid editor.
 */
function shopapp_blocks_localize_editor() {
	wp_localize_script(
		'shopapp-blocks-editor',
		'shopappBlocksEditorSettings',
		array(
			'categories' => shopapp_get_product_category_options(),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'shopapp_blocks_localize_editor' );

/**
 * Registers the plugin's storefront pattern.
 */
function shopapp_blocks_register_patterns() {
	$pattern_file = SHOPAPP_BLOCKS_DIR . 'patterns/storefront.php';

	if ( ! file_exists( $pattern_file ) ) {
		return;
	}

	register_block_pattern_category(
		'shopapp-blocks',
		array( 'label' => __( 'ShopApp', 'shopapp-blocks' ) )
	);

	ob_start();
	include $pattern_file;
	$content = ob_get_clean();

	register_block_pattern(
		'shopapp-blocks/storefront',
		array(
			'title'       => __( 'ShopApp storefront', 'shopapp-blocks' ),
			'description' => __( 'A mobile-app inspired WooCommerce storefront with product grid and quick checkout.', 'shopapp-blocks' ),
			'categories'  => array( 'featured', 'shopapp-blocks' ),
			'blockTypes'  => array( 'shopapp/product-grid' ),
			'content'     => $content,
		)
	);
}
add_action( 'init', 'shopapp_blocks_register_patterns', 20 );

/**
 * Returns the current WooCommerce cart in a frontend-friendly shape.
 *
 * @return array<string,mixed>
 */
function shopapp_blocks_get_cart_summary() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array();
	}

	WC()->cart->calculate_totals();
	$items = array();

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product = isset( $cart_item['data'] ) && is_a( $cart_item['data'], 'WC_Product' ) ? $cart_item['data'] : false;

		if ( ! $product || ! $product->exists() || empty( $cart_item['quantity'] ) ) {
			continue;
		}

		$image_id = $product->get_image_id();
		$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_thumbnail' ) : wc_placeholder_img_src( 'woocommerce_thumbnail' );

		$items[] = array(
			'key'             => $cart_item_key,
			'product_id'      => (int) $cart_item['product_id'],
			'variation_id'    => (int) $cart_item['variation_id'],
			'name'            => $product->get_name(),
			'quantity'        => (int) $cart_item['quantity'],
			'image'           => $image ? $image : wc_placeholder_img_src(),
			'variation'       => wp_strip_all_tags( wc_get_formatted_cart_item_data( $cart_item, true ) ),
			'line_price_html' => wp_kses_post( WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ) ),
		);
	}

	$discount_total = (float) WC()->cart->get_discount_total() + (float) WC()->cart->get_discount_tax();
	$shipping_total = (float) WC()->cart->get_shipping_total() + (float) WC()->cart->get_shipping_tax();
	$tax_total      = (float) WC()->cart->get_total_tax();
	$needs_shipping = WC()->cart->needs_shipping();
	$shipping_html  = __( 'Not required', 'shopapp-blocks' );

	if ( $needs_shipping ) {
		$shipping_html = $shipping_total > 0
			? wp_kses_post( wc_price( $shipping_total ) )
			: __( 'Calculated at checkout', 'shopapp-blocks' );
	}

	return array(
		'items'             => $items,
		'count'             => WC()->cart->get_cart_contents_count(),
		'is_empty'          => WC()->cart->is_empty(),
		'subtotal_html'     => wp_kses_post( WC()->cart->get_cart_subtotal() ),
		'discount_html'     => wp_kses_post( wc_price( $discount_total ) ),
		'show_discount'     => $discount_total > 0,
		'shipping_html'     => $shipping_html,
		'tax_html'          => wp_kses_post( wc_price( $tax_total ) ),
		'show_tax'          => wc_tax_enabled() && $tax_total > 0,
		'total_html'        => wp_kses_post( WC()->cart->get_total() ),
		'applied_coupons'   => WC()->cart->get_applied_coupons(),
		'cart_url'          => wc_get_cart_url(),
		'checkout_url'      => wc_get_checkout_url(),
	);
}

/**
 * Verifies a cart request and ensures the WooCommerce cart is available.
 */
function shopapp_blocks_verify_cart_request() {
	check_ajax_referer( 'shopapp_blocks_cart', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error(
			array( 'message' => __( 'WooCommerce cart is unavailable.', 'shopapp-blocks' ) ),
			400
		);
	}
}

/**
 * Adds a product to the WooCommerce cart.
 */
function shopapp_blocks_ajax_add_to_cart() {
	check_ajax_referer( 'shopapp_blocks_add_to_cart', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error(
			array( 'message' => __( 'WooCommerce cart is unavailable.', 'shopapp-blocks' ) ),
			400
		);
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? max( 1, absint( $_POST['quantity'] ) ) : 1;

	if ( ! $product_id ) {
		wp_send_json_error(
			array( 'message' => __( 'Missing product.', 'shopapp-blocks' ) ),
			400
		);
	}

	$added = WC()->cart->add_to_cart( $product_id, $quantity );

	if ( ! $added ) {
		wp_send_json_error(
			array( 'message' => __( 'Unable to add product to cart.', 'shopapp-blocks' ) ),
			400
		);
	}

	wp_send_json_success(
		array(
			'cart_count' => WC()->cart->get_cart_contents_count(),
			'cart_url'   => wc_get_cart_url(),
			'checkout'   => wc_get_checkout_url(),
			'cart'       => shopapp_blocks_get_cart_summary(),
		)
	);
}
add_action( 'wp_ajax_shopapp_add_to_cart', 'shopapp_blocks_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_shopapp_add_to_cart', 'shopapp_blocks_ajax_add_to_cart' );

/**
 * Returns the current cart.
 */
function shopapp_blocks_ajax_get_cart() {
	shopapp_blocks_verify_cart_request();
	wp_send_json_success( array( 'cart' => shopapp_blocks_get_cart_summary() ) );
}
add_action( 'wp_ajax_shopapp_get_cart', 'shopapp_blocks_ajax_get_cart' );
add_action( 'wp_ajax_nopriv_shopapp_get_cart', 'shopapp_blocks_ajax_get_cart' );

/**
 * Updates a cart line quantity.
 */
function shopapp_blocks_ajax_update_cart_item() {
	shopapp_blocks_verify_cart_request();
	$key      = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';
	$quantity = isset( $_POST['quantity'] ) ? max( 0, absint( $_POST['quantity'] ) ) : 0;
	$cart     = WC()->cart->get_cart();

	if ( ! $key || ! isset( $cart[ $key ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Cart item not found.', 'shopapp-blocks' ) ), 404 );
	}

	WC()->cart->set_quantity( $key, $quantity, true );
	wp_send_json_success( array( 'cart' => shopapp_blocks_get_cart_summary() ) );
}
add_action( 'wp_ajax_shopapp_update_cart_item', 'shopapp_blocks_ajax_update_cart_item' );
add_action( 'wp_ajax_nopriv_shopapp_update_cart_item', 'shopapp_blocks_ajax_update_cart_item' );

/**
 * Removes a cart line.
 */
function shopapp_blocks_ajax_remove_cart_item() {
	shopapp_blocks_verify_cart_request();
	$key = isset( $_POST['key'] ) ? wc_clean( wp_unslash( $_POST['key'] ) ) : '';

	if ( ! $key || ! WC()->cart->remove_cart_item( $key ) ) {
		wp_send_json_error( array( 'message' => __( 'Cart item could not be removed.', 'shopapp-blocks' ) ), 400 );
	}

	wp_send_json_success( array( 'cart' => shopapp_blocks_get_cart_summary() ) );
}
add_action( 'wp_ajax_shopapp_remove_cart_item', 'shopapp_blocks_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_shopapp_remove_cart_item', 'shopapp_blocks_ajax_remove_cart_item' );

/**
 * Applies a coupon to the current cart.
 */
function shopapp_blocks_ajax_apply_coupon() {
	shopapp_blocks_verify_cart_request();
	$coupon = isset( $_POST['coupon'] ) ? wc_format_coupon_code( wc_clean( wp_unslash( $_POST['coupon'] ) ) ) : '';

	if ( ! $coupon ) {
		wp_send_json_error( array( 'message' => __( 'Enter a coupon code.', 'shopapp-blocks' ) ), 400 );
	}

	wc_clear_notices();
	$applied = WC()->cart->apply_coupon( $coupon );

	if ( ! $applied ) {
		$notices = wc_get_notices( 'error' );
		$message = ! empty( $notices[0]['notice'] ) ? wp_strip_all_tags( $notices[0]['notice'] ) : __( 'That coupon could not be applied.', 'shopapp-blocks' );
		wc_clear_notices();
		wp_send_json_error( array( 'message' => $message ), 400 );
	}

	wc_clear_notices();
	wp_send_json_success(
		array(
			'message' => __( 'Coupon applied.', 'shopapp-blocks' ),
			'cart'    => shopapp_blocks_get_cart_summary(),
		)
	);
}
add_action( 'wp_ajax_shopapp_apply_coupon', 'shopapp_blocks_ajax_apply_coupon' );
add_action( 'wp_ajax_nopriv_shopapp_apply_coupon', 'shopapp_blocks_ajax_apply_coupon' );

/**
 * Sanitizes a list of saved WooCommerce product IDs.
 *
 * @param mixed $raw_ids Product IDs.
 * @return int[]
 */
function shopapp_blocks_sanitize_saved_product_ids( $raw_ids ) {
	if ( is_string( $raw_ids ) ) {
		$raw_ids = explode( ',', $raw_ids );
	}

	if ( ! is_array( $raw_ids ) ) {
		return array();
	}

	$ids = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $raw_ids ) ) ) ), 0, 100 );

	return array_values(
		array_filter(
			$ids,
			static function ( $product_id ) {
				$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : false;
				return $product && 'publish' === $product->get_status() && $product->is_visible();
			}
		)
	);
}

/**
 * Syncs saved products and returns their card data.
 */
function shopapp_blocks_ajax_sync_saved_products() {
	check_ajax_referer( 'shopapp_blocks_navigation', 'nonce' );
	$incoming = isset( $_POST['product_ids'] ) ? wp_unslash( $_POST['product_ids'] ) : array();
	$ids      = shopapp_blocks_sanitize_saved_product_ids( $incoming );
	$mode     = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'merge';

	if ( is_user_logged_in() ) {
		$stored = shopapp_blocks_sanitize_saved_product_ids( get_user_meta( get_current_user_id(), '_shopapp_saved_products', true ) );
		$ids    = 'replace' === $mode ? $ids : shopapp_blocks_sanitize_saved_product_ids( array_merge( $stored, $ids ) );
		update_user_meta( get_current_user_id(), '_shopapp_saved_products', $ids );
	}

	$products = array();
	foreach ( $ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			$products[] = shopapp_get_product_data( $product );
		}
	}

	wp_send_json_success(
		array(
			'ids'        => $ids,
			'products'   => $products,
			'isLoggedIn' => is_user_logged_in(),
		)
	);
}
add_action( 'wp_ajax_shopapp_sync_saved_products', 'shopapp_blocks_ajax_sync_saved_products' );
add_action( 'wp_ajax_nopriv_shopapp_sync_saved_products', 'shopapp_blocks_ajax_sync_saved_products' );

/**
 * Returns the current customer's account drawer summary.
 */
function shopapp_blocks_ajax_get_account_summary() {
	check_ajax_referer( 'shopapp_blocks_navigation', 'nonce' );
	$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : wp_login_url();

	if ( ! is_user_logged_in() ) {
		wp_send_json_success(
			array(
				'isLoggedIn'    => false,
				'loginUrl'      => $account_url,
				'registerUrl'   => $account_url,
				'lostPasswordUrl' => function_exists( 'wc_lostpassword_url' ) ? wc_lostpassword_url() : wp_lostpassword_url(),
			)
		);
	}

	$user   = wp_get_current_user();
	$orders = function_exists( 'wc_get_orders' ) ? wc_get_orders(
		array(
			'customer_id' => $user->ID,
			'limit'       => 3,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	) : array();
	$order_data = array();

	foreach ( $orders as $order ) {
		$order_data[] = array(
			'number' => $order->get_order_number(),
			'date'   => $order->get_date_created() ? wc_format_datetime( $order->get_date_created() ) : '',
			'status' => wc_get_order_status_name( $order->get_status() ),
			'total'  => wp_kses_post( $order->get_formatted_order_total() ),
			'url'    => $order->get_view_order_url(),
		);
	}

	wp_send_json_success(
		array(
			'isLoggedIn' => true,
			'name'       => $user->display_name,
			'email'      => $user->user_email,
			'avatar'     => get_avatar_url( $user->ID, array( 'size' => 96 ) ),
			'orders'     => $order_data,
			'links'      => array(
				'account'  => $account_url,
				'orders'   => wc_get_account_endpoint_url( 'orders' ),
				'downloads' => wc_get_account_endpoint_url( 'downloads' ),
				'addresses' => wc_get_account_endpoint_url( 'edit-address' ),
				'details'   => wc_get_account_endpoint_url( 'edit-account' ),
				'payments'  => wc_get_account_endpoint_url( 'payment-methods' ),
				'logout'    => wc_logout_url(),
			),
		)
	);
}
add_action( 'wp_ajax_shopapp_get_account_summary', 'shopapp_blocks_ajax_get_account_summary' );
add_action( 'wp_ajax_nopriv_shopapp_get_account_summary', 'shopapp_blocks_ajax_get_account_summary' );

/**
 * Loads a page of product cards for Product Grid pagination.
 */
function shopapp_blocks_ajax_load_products() {
	check_ajax_referer( 'shopapp_blocks_load_products', 'nonce' );

	$query = isset( $_POST['query'] ) ? wp_unslash( $_POST['query'] ) : '{}';
	$query = json_decode( (string) $query, true );
	$query = is_array( $query ) ? $query : array();
	$page  = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;

	$query['page'] = $page;
	$products      = shopapp_get_storefront_products( $query );

	ob_start();
	foreach ( $products as $product ) {
		shopapp_render_product_card( $product, $query );
	}

	wp_send_json_success( array( 'html' => ob_get_clean() ) );
}
add_action( 'wp_ajax_shopapp_load_products', 'shopapp_blocks_ajax_load_products' );
add_action( 'wp_ajax_nopriv_shopapp_load_products', 'shopapp_blocks_ajax_load_products' );

require_once SHOPAPP_BLOCKS_DIR . 'inc/template-functions.php';
require_once SHOPAPP_BLOCKS_DIR . 'inc/blocks.php';
