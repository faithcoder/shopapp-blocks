<?php
/**
 * Custom block registration.
 *
 * @package ShopAppBlocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers ShopApp block category.
 *
 * @param array<int,array<string,string>> $categories Block categories.
 * @return array<int,array<string,string>>
 */
function shopapp_register_block_category( $categories ) {
	$categories[] = array(
		'slug'  => 'shopapp-blocks',
		'title' => __( 'ShopApp', 'shopapp-blocks' ),
	);

	return $categories;
}
add_filter( 'block_categories_all', 'shopapp_register_block_category' );

/**
 * Gets block supports shared by ShopApp blocks.
 *
 * @return array<string,mixed>
 */
function shopapp_get_storefront_block_supports() {
	return array(
		'align'           => array( 'wide', 'full' ),
		'anchor'          => true,
		'border'          => array(
			'color'  => true,
			'radius' => true,
			'style'  => true,
			'width'  => true,
		),
		'color'           => array(
			'background' => true,
			'gradients'  => true,
			'link'       => true,
			'text'       => true,
		),
		'customClassName' => true,
		'dimensions'      => array(
			'minHeight' => true,
		),
		'html'            => false,
		'spacing'         => array(
			'margin'  => true,
			'padding' => true,
		),
		'typography'      => array(
			'fontFamily'     => true,
			'fontSize'       => true,
			'fontStyle'      => true,
			'fontWeight'     => true,
			'letterSpacing'  => true,
			'lineHeight'     => true,
			'textDecoration' => true,
			'textTransform'  => true,
		),
	);
}

/**
 * Registers custom blocks.
 */
function shopapp_register_blocks() {
	register_block_type(
		'shopapp/product-grid',
		array(
			'api_version'     => 2,
			'title'           => __( 'ShopApp Product Grid', 'shopapp-blocks' ),
			'category'        => 'shopapp-blocks',
			'icon'            => 'products',
			'description'     => __( 'Mobile-app styled WooCommerce storefront with product grid and quick checkout drawer.', 'shopapp-blocks' ),
			'editor_script'   => 'shopapp-blocks-editor',
			'style'           => 'shopapp-blocks',
			'view_script'     => 'shopapp-blocks-frontend',
			'render_callback' => 'shopapp_render_storefront',
			'supports'        => shopapp_get_storefront_block_supports(),
			'attributes'      => array(
				'sectionTitle'   => array( 'type' => 'string', 'default' => __( 'Curated for you', 'shopapp-blocks' ) ),
				'category'       => array( 'type' => 'string', 'default' => '' ),
				'orderby'        => array( 'type' => 'string', 'default' => 'date' ),
				'perPage'        => array( 'type' => 'number', 'default' => 12 ),
				'columns'        => array( 'type' => 'number', 'default' => 4 ),
				'tabletColumns'  => array( 'type' => 'number', 'default' => 2 ),
				'mobileColumns'  => array( 'type' => 'number', 'default' => 2 ),
				'pagination'     => array( 'type' => 'string', 'default' => 'load-more' ),
				'showRatings'    => array( 'type' => 'boolean', 'default' => true ),
				'showCategory'   => array( 'type' => 'boolean', 'default' => true ),
				'showOnSale'     => array( 'type' => 'boolean', 'default' => true ),
				'cardRadius'     => array( 'type' => 'number', 'default' => 34 ),
				'infoBackground' => array( 'type' => 'string', 'default' => '' ),
				'infoColor'      => array( 'type' => 'string', 'default' => '' ),
				'ratingStarColor' => array( 'type' => 'string', 'default' => '' ),
				'ratingTextColor' => array( 'type' => 'string', 'default' => '' ),
				'categoryColor'  => array( 'type' => 'string', 'default' => '' ),
				'onSaleBackground' => array( 'type' => 'string', 'default' => '' ),
				'onSaleColor'    => array( 'type' => 'string', 'default' => '' ),
				'titleColor'     => array( 'type' => 'string', 'default' => '' ),
				'priceColor'     => array( 'type' => 'string', 'default' => '' ),
				'currencyColor'  => array( 'type' => 'string', 'default' => '' ),
				'addButtonBackground' => array( 'type' => 'string', 'default' => '' ),
				'addButtonColor' => array( 'type' => 'string', 'default' => '' ),
				'loadMoreBackground' => array( 'type' => 'string', 'default' => '' ),
				'loadMoreColor'  => array( 'type' => 'string', 'default' => '' ),
				'paginationBackground' => array( 'type' => 'string', 'default' => '' ),
				'paginationColor' => array( 'type' => 'string', 'default' => '' ),
				'paginationActiveBackground' => array( 'type' => 'string', 'default' => '' ),
				'paginationActiveColor' => array( 'type' => 'string', 'default' => '' ),
			),
		)
	);

	register_block_type(
		'shopapp/search-filters',
		array(
			'api_version'     => 2,
			'title'           => __( 'ShopApp Search & Filters', 'shopapp-blocks' ),
			'category'        => 'shopapp-blocks',
			'icon'            => 'search',
			'description'     => __( 'Storefront heading, product search, and category filters.', 'shopapp-blocks' ),
			'editor_script'   => 'shopapp-blocks-editor',
			'style'           => 'shopapp-blocks',
			'view_script'     => 'shopapp-blocks-frontend',
			'render_callback' => 'shopapp_render_search_filters',
			'supports'        => shopapp_get_storefront_block_supports(),
			'attributes'      => array(
				'brand'       => array( 'type' => 'string', 'default' => __( 'Northbound', 'shopapp-blocks' ) ),
				'greeting'    => array( 'type' => 'string', 'default' => __( 'Good morning, Alex', 'shopapp-blocks' ) ),
				'placeholder' => array( 'type' => 'string', 'default' => __( 'Search essentials', 'shopapp-blocks' ) ),
				'showHeader'  => array( 'type' => 'boolean', 'default' => true ),
				'showSearch'  => array( 'type' => 'boolean', 'default' => true ),
				'showFilters' => array( 'type' => 'boolean', 'default' => true ),
			),
		)
	);

	register_block_type(
		'shopapp/bottom-nav',
		array(
			'api_version'     => 2,
			'title'           => __( 'ShopApp Checkout', 'shopapp-blocks' ),
			'category'        => 'shopapp-blocks',
			'icon'            => 'cart',
			'description'     => __( 'Custom floating checkout bar with popup panels and quick checkout.', 'shopapp-blocks' ),
			'editor_script'   => 'shopapp-blocks-editor',
			'style'           => 'shopapp-blocks',
			'view_script'     => 'shopapp-blocks-frontend',
			'render_callback' => 'shopapp_render_bottom_nav',
			'supports'        => shopapp_get_storefront_block_supports(),
			'attributes'      => array(
				'showLabels'       => array( 'type' => 'boolean', 'default' => true ),
				'showCart'         => array( 'type' => 'boolean', 'default' => true ),
				'menuItems'        => array( 'type' => 'array', 'default' => array() ),
				'width'            => array( 'type' => 'number', 'default' => 660 ),
				'height'           => array( 'type' => 'number', 'default' => 88 ),
				'padding'          => array( 'type' => 'number', 'default' => 10 ),
				'gap'              => array( 'type' => 'number', 'default' => 8 ),
				'borderRadius'     => array( 'type' => 'number', 'default' => 38 ),
				'borderWidth'      => array( 'type' => 'number', 'default' => 1 ),
				'blur'             => array( 'type' => 'number', 'default' => 20 ),
				'saturate'         => array( 'type' => 'number', 'default' => 160 ),
				'opacity'          => array( 'type' => 'number', 'default' => 94 ),
				'zIndex'           => array( 'type' => 'number', 'default' => 30 ),
				'iconSize'         => array( 'type' => 'number', 'default' => 31 ),
				'labelSize'        => array( 'type' => 'number', 'default' => 14 ),
				'labelWeight'      => array( 'type' => 'number', 'default' => 800 ),
				'cartSize'         => array( 'type' => 'number', 'default' => 88 ),
				'cartIconSize'     => array( 'type' => 'number', 'default' => 39 ),
				'cartOffsetY'      => array( 'type' => 'number', 'default' => -50 ),
				'enableBuzz'       => array( 'type' => 'boolean', 'default' => true ),
				'backgroundColor'  => array( 'type' => 'string', 'default' => '' ),
				'textColor'        => array( 'type' => 'string', 'default' => '' ),
				'borderColor'      => array( 'type' => 'string', 'default' => '' ),
				'iconColor'        => array( 'type' => 'string', 'default' => '' ),
				'labelColor'       => array( 'type' => 'string', 'default' => '' ),
				'activeColor'      => array( 'type' => 'string', 'default' => '' ),
				'cartBackground'   => array( 'type' => 'string', 'default' => '' ),
				'cartColor'        => array( 'type' => 'string', 'default' => '' ),
				'countBackground'  => array( 'type' => 'string', 'default' => '' ),
				'countColor'       => array( 'type' => 'string', 'default' => '' ),
			),
		)
	);
}
add_action( 'init', 'shopapp_register_blocks' );
