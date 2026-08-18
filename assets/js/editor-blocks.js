(function (wp, settings) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelColorSettings = wp.blockEditor.PanelColorSettings;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var Button = wp.components.Button;
	var RangeControl = wp.components.RangeControl;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var ToggleControl = wp.components.ToggleControl;
	var ServerSideRender = wp.serverSideRender && (wp.serverSideRender.default || wp.serverSideRender);
	var __ = wp.i18n.__;
	var categoryOptions = settings && Array.isArray(settings.categories) && settings.categories.length ? settings.categories : [
		{ label: __('All categories', 'shopapp-blocks'), value: '' }
	];
	var checkoutIconOptions = [
		{ label: __('Home', 'shopapp-blocks'), value: 'home' },
		{ label: __('Search', 'shopapp-blocks'), value: 'search' },
		{ label: __('Bag', 'shopapp-blocks'), value: 'bag' },
		{ label: __('Heart', 'shopapp-blocks'), value: 'heart' },
		{ label: __('User', 'shopapp-blocks'), value: 'user' },
		{ label: __('Bell', 'shopapp-blocks'), value: 'bell' },
		{ label: __('Card', 'shopapp-blocks'), value: 'card' },
		{ label: __('Check', 'shopapp-blocks'), value: 'check' },
		{ label: __('Shield', 'shopapp-blocks'), value: 'shield' },
		{ label: __('Star', 'shopapp-blocks'), value: 'star' },
		{ label: __('Truck', 'shopapp-blocks'), value: 'truck' },
		{ label: __('Zap', 'shopapp-blocks'), value: 'zap' }
	];
	var checkoutPopupOptions = [
		{ label: __('Shop popup', 'shopapp-blocks'), value: 'shop' },
		{ label: __('Search popup', 'shopapp-blocks'), value: 'search' },
		{ label: __('Saved popup', 'shopapp-blocks'), value: 'saved' },
		{ label: __('You popup', 'shopapp-blocks'), value: 'you' }
	];
	var defaultCheckoutItems = [
		{ label: 'Shop', icon: 'home', link: '', popup: 'shop', visible: true, active: true },
		{ label: 'Search', icon: 'search', link: '', popup: 'search', visible: true, active: false },
		{ label: 'Saved', icon: 'heart', link: '', popup: 'saved', visible: true, active: false },
		{ label: 'You', icon: 'user', link: '', popup: 'you', visible: true, active: false }
	];

	var supports = {
		align: ['wide', 'full'],
		anchor: true,
		border: { color: true, radius: true, style: true, width: true },
		color: { background: true, gradients: true, link: true, text: true },
		customClassName: true,
		dimensions: { minHeight: true },
		html: false,
		spacing: { margin: true, padding: true },
		typography: {
			fontFamily: true,
			fontSize: true,
			fontStyle: true,
			fontWeight: true,
			letterSpacing: true,
			lineHeight: true,
			textDecoration: true,
			textTransform: true
		}
	};

	function setAttr(setAttributes, key) {
		return function (value) {
			var update = {};
			update[key] = value;
			setAttributes(update);
		};
	}

	function preview(block, attrs, className) {
		return el(
			'div',
			useBlockProps({ className: (className || '') + ' alignfull' }),
			ServerSideRender ? el(ServerSideRender, { block: block, attributes: attrs }) : el('p', null, __('ShopApp preview', 'shopapp-blocks'))
		);
	}

	function checkoutItems(attrs) {
		return Array.isArray(attrs.menuItems) && attrs.menuItems.length ? attrs.menuItems : defaultCheckoutItems;
	}

	function updateCheckoutItem(setAttributes, attrs, index, key, value) {
		var items = checkoutItems(attrs).map(function (item) {
			return Object.assign({}, item);
		});
		items[index][key] = value;
		setAttributes({ menuItems: items });
	}

	function addCheckoutItem(setAttributes, attrs) {
		var items = checkoutItems(attrs).map(function (item) {
			return Object.assign({}, item);
		});
		items.push({ label: __('New item', 'shopapp-blocks'), icon: 'star', link: '', popup: 'shop', visible: true, active: false });
		setAttributes({ menuItems: items });
	}

	function removeCheckoutItem(setAttributes, attrs, index) {
		var items = checkoutItems(attrs).filter(function (item, itemIndex) {
			return itemIndex !== index;
		});
		setAttributes({ menuItems: items });
	}

	wp.blocks.registerBlockType('shopapp/search-filters', {
		apiVersion: 2,
		title: __('ShopApp Search & Filters', 'shopapp-blocks'),
		icon: 'search',
		category: 'shopapp-blocks',
		description: __('Storefront heading, product search, and category filters.', 'shopapp-blocks'),
		supports: supports,
		attributes: {
			brand: { type: 'string', default: 'Northbound' },
			greeting: { type: 'string', default: 'Good morning, Alex' },
			placeholder: { type: 'string', default: 'Search essentials' },
			showHeader: { type: 'boolean', default: true },
			showSearch: { type: 'boolean', default: true },
			showFilters: { type: 'boolean', default: true }
		},
		edit: function (props) {
			var attrs = props.attributes;
			var setAttributes = props.setAttributes;
			return el(
				Fragment,
				null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Search & filters', 'shopapp-blocks'), initialOpen: true },
						el(TextControl, { label: __('Brand', 'shopapp-blocks'), value: attrs.brand || '', onChange: setAttr(setAttributes, 'brand') }),
						el(TextControl, { label: __('Greeting', 'shopapp-blocks'), value: attrs.greeting || '', onChange: setAttr(setAttributes, 'greeting') }),
						el(TextControl, { label: __('Search placeholder', 'shopapp-blocks'), value: attrs.placeholder || '', onChange: setAttr(setAttributes, 'placeholder') }),
						el(ToggleControl, { label: __('Show header', 'shopapp-blocks'), checked: attrs.showHeader, onChange: setAttr(setAttributes, 'showHeader') }),
						el(ToggleControl, { label: __('Show search', 'shopapp-blocks'), checked: attrs.showSearch, onChange: setAttr(setAttributes, 'showSearch') }),
						el(ToggleControl, { label: __('Show category filters', 'shopapp-blocks'), checked: attrs.showFilters, onChange: setAttr(setAttributes, 'showFilters') })
					)
				),
				preview('shopapp/search-filters', attrs, 'shopapp-editor-preview')
			);
		},
		save: function () { return null; }
	});

	wp.blocks.registerBlockType('shopapp/product-grid', {
		apiVersion: 2,
		title: __('ShopApp Product Grid', 'shopapp-blocks'),
		icon: 'products',
		category: 'shopapp-blocks',
		description: __('WooCommerce product grid only, with responsive columns and pagination.', 'shopapp-blocks'),
		supports: supports,
		attributes: {
			sectionTitle: { type: 'string', default: 'Curated for you' },
			category: { type: 'string', default: '' },
			orderby: { type: 'string', default: 'date' },
			perPage: { type: 'number', default: 12 },
			columns: { type: 'number', default: 4 },
			tabletColumns: { type: 'number', default: 2 },
			mobileColumns: { type: 'number', default: 2 },
			pagination: { type: 'string', default: 'load-more' },
			showRatings: { type: 'boolean', default: true },
			showCategory: { type: 'boolean', default: true },
			showOnSale: { type: 'boolean', default: true },
			cardRadius: { type: 'number', default: 34 },
			infoBackground: { type: 'string', default: '' },
			infoColor: { type: 'string', default: '' },
			ratingStarColor: { type: 'string', default: '' },
			ratingTextColor: { type: 'string', default: '' },
			categoryColor: { type: 'string', default: '' },
			onSaleBackground: { type: 'string', default: '' },
			onSaleColor: { type: 'string', default: '' },
			titleColor: { type: 'string', default: '' },
			priceColor: { type: 'string', default: '' },
			currencyColor: { type: 'string', default: '' },
			addButtonBackground: { type: 'string', default: '' },
			addButtonColor: { type: 'string', default: '' },
			loadMoreBackground: { type: 'string', default: '' },
			loadMoreColor: { type: 'string', default: '' },
			paginationBackground: { type: 'string', default: '' },
			paginationColor: { type: 'string', default: '' },
			paginationActiveBackground: { type: 'string', default: '' },
			paginationActiveColor: { type: 'string', default: '' }
		},
		edit: function (props) {
			var attrs = props.attributes;
			var setAttributes = props.setAttributes;
			return el(
				Fragment,
				null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Product query', 'shopapp-blocks'), initialOpen: true },
						el(TextControl, { label: __('Section title', 'shopapp-blocks'), value: attrs.sectionTitle || '', onChange: setAttr(setAttributes, 'sectionTitle') }),
						el(SelectControl, { label: __('Product category', 'shopapp-blocks'), value: attrs.category || '', options: categoryOptions, onChange: setAttr(setAttributes, 'category') }),
						el(SelectControl, {
							label: __('Order by', 'shopapp-blocks'),
							value: attrs.orderby || 'date',
							options: [
								{ label: __('Newest', 'shopapp-blocks'), value: 'date' },
								{ label: __('Name', 'shopapp-blocks'), value: 'name' },
								{ label: __('Price', 'shopapp-blocks'), value: 'price' },
								{ label: __('Rating', 'shopapp-blocks'), value: 'rating' },
								{ label: __('Popularity', 'shopapp-blocks'), value: 'popularity' }
							],
							onChange: setAttr(setAttributes, 'orderby')
						}),
						el(TextControl, { label: __('Products per page', 'shopapp-blocks'), type: 'number', min: 1, value: attrs.perPage || 12, onChange: function (value) { setAttributes({ perPage: Math.max(1, parseInt(value || 1, 10)) }); } }),
						el(SelectControl, {
							label: __('Pagination', 'shopapp-blocks'),
							value: attrs.pagination || 'load-more',
							options: [
								{ label: __('Load More (AJAX)', 'shopapp-blocks'), value: 'load-more' },
								{ label: __('Numbered pagination', 'shopapp-blocks'), value: 'pagination' },
								{ label: __('None', 'shopapp-blocks'), value: 'none' }
							],
							onChange: setAttr(setAttributes, 'pagination')
						}),
						el(ToggleControl, { label: __('Show ratings', 'shopapp-blocks'), checked: attrs.showRatings !== false, onChange: setAttr(setAttributes, 'showRatings') }),
						el(ToggleControl, { label: __('Show category', 'shopapp-blocks'), checked: attrs.showCategory !== false, onChange: setAttr(setAttributes, 'showCategory') }),
						el(ToggleControl, { label: __('Show On Sale badge', 'shopapp-blocks'), checked: attrs.showOnSale !== false, onChange: setAttr(setAttributes, 'showOnSale') })
					),
					el(PanelBody, { title: __('Responsive columns', 'shopapp-blocks'), initialOpen: false },
						el(RangeControl, { label: __('Desktop columns', 'shopapp-blocks'), value: attrs.columns || 4, min: 1, max: 12, onChange: setAttr(setAttributes, 'columns') }),
						el(RangeControl, { label: __('Tablet columns', 'shopapp-blocks'), value: attrs.tabletColumns || 2, min: 1, max: 8, onChange: setAttr(setAttributes, 'tabletColumns') }),
						el(RangeControl, { label: __('Mobile columns', 'shopapp-blocks'), value: attrs.mobileColumns || 2, min: 1, max: 4, onChange: setAttr(setAttributes, 'mobileColumns') })
					)
				),
				el(InspectorControls, { group: 'styles' },
					el(PanelBody, { title: __('Card style', 'shopapp-blocks'), initialOpen: false },
						el(RangeControl, { label: __('Product card border radius', 'shopapp-blocks'), value: attrs.cardRadius || 34, min: 0, max: 80, onChange: setAttr(setAttributes, 'cardRadius') })
					),
					PanelColorSettings && el(PanelColorSettings, {
						title: __('Product info colors', 'shopapp-blocks'),
						initialOpen: false,
						colorSettings: [
							{ label: __('Info background', 'shopapp-blocks'), value: attrs.infoBackground, onChange: setAttr(setAttributes, 'infoBackground') },
							{ label: __('Info text', 'shopapp-blocks'), value: attrs.infoColor, onChange: setAttr(setAttributes, 'infoColor') },
							{ label: __('Rating star', 'shopapp-blocks'), value: attrs.ratingStarColor, onChange: setAttr(setAttributes, 'ratingStarColor') },
							{ label: __('Rating text', 'shopapp-blocks'), value: attrs.ratingTextColor, onChange: setAttr(setAttributes, 'ratingTextColor') },
							{ label: __('Category', 'shopapp-blocks'), value: attrs.categoryColor, onChange: setAttr(setAttributes, 'categoryColor') },
							{ label: __('On Sale background', 'shopapp-blocks'), value: attrs.onSaleBackground, onChange: setAttr(setAttributes, 'onSaleBackground') },
							{ label: __('On Sale text', 'shopapp-blocks'), value: attrs.onSaleColor, onChange: setAttr(setAttributes, 'onSaleColor') },
							{ label: __('Product title', 'shopapp-blocks'), value: attrs.titleColor, onChange: setAttr(setAttributes, 'titleColor') },
							{ label: __('Price', 'shopapp-blocks'), value: attrs.priceColor, onChange: setAttr(setAttributes, 'priceColor') },
							{ label: __('Currency', 'shopapp-blocks'), value: attrs.currencyColor, onChange: setAttr(setAttributes, 'currencyColor') },
							{ label: __('Plus button background', 'shopapp-blocks'), value: attrs.addButtonBackground, onChange: setAttr(setAttributes, 'addButtonBackground') },
							{ label: __('Plus button icon', 'shopapp-blocks'), value: attrs.addButtonColor, onChange: setAttr(setAttributes, 'addButtonColor') }
						]
					}),
					PanelColorSettings && el(PanelColorSettings, {
						title: __('Pagination colors', 'shopapp-blocks'),
						initialOpen: false,
						colorSettings: [
							{ label: __('Load More background', 'shopapp-blocks'), value: attrs.loadMoreBackground, onChange: setAttr(setAttributes, 'loadMoreBackground') },
							{ label: __('Load More text', 'shopapp-blocks'), value: attrs.loadMoreColor, onChange: setAttr(setAttributes, 'loadMoreColor') },
							{ label: __('Pagination background', 'shopapp-blocks'), value: attrs.paginationBackground, onChange: setAttr(setAttributes, 'paginationBackground') },
							{ label: __('Pagination text', 'shopapp-blocks'), value: attrs.paginationColor, onChange: setAttr(setAttributes, 'paginationColor') },
							{ label: __('Active pagination background', 'shopapp-blocks'), value: attrs.paginationActiveBackground, onChange: setAttr(setAttributes, 'paginationActiveBackground') },
							{ label: __('Active pagination text', 'shopapp-blocks'), value: attrs.paginationActiveColor, onChange: setAttr(setAttributes, 'paginationActiveColor') }
						]
					})
				),
				preview('shopapp/product-grid', attrs, 'shopapp-editor-preview')
			);
		},
		save: function () { return null; }
	});

	wp.blocks.registerBlockType('shopapp/bottom-nav', {
		apiVersion: 2,
		title: __('ShopApp Checkout', 'shopapp-blocks'),
		icon: 'cart',
		category: 'shopapp-blocks',
		description: __('Custom floating checkout bar with popup panels and quick checkout.', 'shopapp-blocks'),
		supports: supports,
		attributes: {
			showLabels: { type: 'boolean', default: true },
			showCart: { type: 'boolean', default: true },
			menuItems: { type: 'array', default: [] },
			width: { type: 'number', default: 660 },
			height: { type: 'number', default: 88 },
			padding: { type: 'number', default: 10 },
			gap: { type: 'number', default: 8 },
			borderRadius: { type: 'number', default: 38 },
			borderWidth: { type: 'number', default: 1 },
			blur: { type: 'number', default: 20 },
			saturate: { type: 'number', default: 160 },
			opacity: { type: 'number', default: 94 },
			zIndex: { type: 'number', default: 30 },
			iconSize: { type: 'number', default: 31 },
			labelSize: { type: 'number', default: 14 },
			labelWeight: { type: 'number', default: 800 },
			cartSize: { type: 'number', default: 88 },
			cartIconSize: { type: 'number', default: 39 },
			cartOffsetY: { type: 'number', default: -50 },
			enableBuzz: { type: 'boolean', default: true },
			backgroundColor: { type: 'string', default: '' },
			textColor: { type: 'string', default: '' },
			borderColor: { type: 'string', default: '' },
			iconColor: { type: 'string', default: '' },
			labelColor: { type: 'string', default: '' },
			activeColor: { type: 'string', default: '' },
			cartBackground: { type: 'string', default: '' },
			cartColor: { type: 'string', default: '' },
			countBackground: { type: 'string', default: '' },
			countColor: { type: 'string', default: '' }
		},
		edit: function (props) {
			var attrs = props.attributes;
			var setAttributes = props.setAttributes;
			var items = checkoutItems(attrs);
			return el(
				Fragment,
				null,
				el(InspectorControls, null,
					el(PanelBody, { title: __('Checkout bar', 'shopapp-blocks'), initialOpen: true },
						el(ToggleControl, { label: __('Show labels', 'shopapp-blocks'), checked: attrs.showLabels !== false, onChange: setAttr(setAttributes, 'showLabels') }),
						el(ToggleControl, { label: __('Show cart button', 'shopapp-blocks'), checked: attrs.showCart !== false, onChange: setAttr(setAttributes, 'showCart') })
					),
					el(PanelBody, { title: __('Menu items', 'shopapp-blocks'), initialOpen: false },
						items.map(function (item, index) {
							return el('div', { key: index, className: 'shopapp-editor-menu-item' },
								el(ToggleControl, { label: __('Show item', 'shopapp-blocks') + ' ' + (index + 1), checked: item.visible !== false, onChange: function (value) { updateCheckoutItem(setAttributes, attrs, index, 'visible', value); } }),
								el(TextControl, { label: __('Text', 'shopapp-blocks'), value: item.label || '', onChange: function (value) { updateCheckoutItem(setAttributes, attrs, index, 'label', value); } }),
								el(SelectControl, { label: __('Icon', 'shopapp-blocks'), value: item.icon || 'home', options: checkoutIconOptions, onChange: function (value) { updateCheckoutItem(setAttributes, attrs, index, 'icon', value); } }),
								el(TextControl, { label: __('Link URL', 'shopapp-blocks'), value: item.link || '', help: __('Leave empty to open a popup.', 'shopapp-blocks'), onChange: function (value) { updateCheckoutItem(setAttributes, attrs, index, 'link', value); } }),
								el(SelectControl, { label: __('Popup target', 'shopapp-blocks'), value: item.popup || 'shop', options: checkoutPopupOptions, onChange: function (value) { updateCheckoutItem(setAttributes, attrs, index, 'popup', value); } }),
								el(ToggleControl, { label: __('Active item', 'shopapp-blocks'), checked: !!item.active, onChange: function (value) { updateCheckoutItem(setAttributes, attrs, index, 'active', value); } }),
								el(Button, { isDestructive: true, variant: 'secondary', onClick: function () { removeCheckoutItem(setAttributes, attrs, index); } }, __('Remove item', 'shopapp-blocks'))
							);
						}),
						el(Button, { variant: 'primary', onClick: function () { addCheckoutItem(setAttributes, attrs); } }, __('Add menu item', 'shopapp-blocks'))
					),
					el(PanelBody, { title: __('Layout', 'shopapp-blocks'), initialOpen: false },
						el(RangeControl, { label: __('Width', 'shopapp-blocks'), value: attrs.width || 660, min: 240, max: 1400, onChange: setAttr(setAttributes, 'width') }),
						el(RangeControl, { label: __('Height', 'shopapp-blocks'), value: attrs.height || 88, min: 56, max: 220, onChange: setAttr(setAttributes, 'height') }),
						el(RangeControl, { label: __('Padding', 'shopapp-blocks'), value: attrs.padding || 10, min: 0, max: 48, onChange: setAttr(setAttributes, 'padding') }),
						el(RangeControl, { label: __('Item gap', 'shopapp-blocks'), value: attrs.gap || 8, min: 0, max: 40, onChange: setAttr(setAttributes, 'gap') }),
						el(RangeControl, { label: __('Layer z-index', 'shopapp-blocks'), value: attrs.zIndex || 30, min: 1, max: 9999, onChange: setAttr(setAttributes, 'zIndex') })
					)
				),
				el(InspectorControls, { group: 'styles' },
					el(PanelBody, { title: __('Shape and glass', 'shopapp-blocks'), initialOpen: false },
						el(RangeControl, { label: __('Border radius', 'shopapp-blocks'), value: attrs.borderRadius || 38, min: 0, max: 120, onChange: setAttr(setAttributes, 'borderRadius') }),
						el(RangeControl, { label: __('Border width', 'shopapp-blocks'), value: attrs.borderWidth || 1, min: 0, max: 12, onChange: setAttr(setAttributes, 'borderWidth') }),
						el(RangeControl, { label: __('Glass blur', 'shopapp-blocks'), value: attrs.blur || 20, min: 0, max: 60, onChange: setAttr(setAttributes, 'blur') }),
						el(RangeControl, { label: __('Glass saturation', 'shopapp-blocks'), value: attrs.saturate || 160, min: 0, max: 300, onChange: setAttr(setAttributes, 'saturate') }),
						el(RangeControl, { label: __('Background opacity', 'shopapp-blocks'), value: attrs.opacity || 94, min: 0, max: 100, onChange: setAttr(setAttributes, 'opacity') })
					),
					el(PanelBody, { title: __('Typography and icons', 'shopapp-blocks'), initialOpen: false },
						el(RangeControl, { label: __('Icon size', 'shopapp-blocks'), value: attrs.iconSize || 31, min: 12, max: 80, onChange: setAttr(setAttributes, 'iconSize') }),
						el(RangeControl, { label: __('Menu text size', 'shopapp-blocks'), value: attrs.labelSize || 14, min: 10, max: 32, onChange: setAttr(setAttributes, 'labelSize') }),
						el(RangeControl, { label: __('Menu text weight', 'shopapp-blocks'), value: attrs.labelWeight || 800, min: 100, max: 900, step: 100, onChange: setAttr(setAttributes, 'labelWeight') })
					),
					el(PanelBody, { title: __('Cart button', 'shopapp-blocks'), initialOpen: false },
						el(RangeControl, { label: __('Cart button size', 'shopapp-blocks'), value: attrs.cartSize || 88, min: 48, max: 160, onChange: setAttr(setAttributes, 'cartSize') }),
						el(RangeControl, { label: __('Cart icon size', 'shopapp-blocks'), value: attrs.cartIconSize || 39, min: 18, max: 96, onChange: setAttr(setAttributes, 'cartIconSize') }),
						el(RangeControl, { label: __('Cart vertical offset', 'shopapp-blocks'), value: attrs.cartOffsetY || -50, min: -140, max: 80, onChange: setAttr(setAttributes, 'cartOffsetY') }),
						el(ToggleControl, { label: __('Buzz animation on add to cart', 'shopapp-blocks'), checked: attrs.enableBuzz !== false, onChange: setAttr(setAttributes, 'enableBuzz') })
					),
					PanelColorSettings && el(PanelColorSettings, {
						title: __('Checkout bar colors', 'shopapp-blocks'),
						initialOpen: false,
						colorSettings: [
							{ label: __('Background', 'shopapp-blocks'), value: attrs.backgroundColor, onChange: setAttr(setAttributes, 'backgroundColor') },
							{ label: __('Base text', 'shopapp-blocks'), value: attrs.textColor, onChange: setAttr(setAttributes, 'textColor') },
							{ label: __('Border', 'shopapp-blocks'), value: attrs.borderColor, onChange: setAttr(setAttributes, 'borderColor') },
							{ label: __('Menu icon', 'shopapp-blocks'), value: attrs.iconColor, onChange: setAttr(setAttributes, 'iconColor') },
							{ label: __('Menu text', 'shopapp-blocks'), value: attrs.labelColor, onChange: setAttr(setAttributes, 'labelColor') },
							{ label: __('Active item', 'shopapp-blocks'), value: attrs.activeColor, onChange: setAttr(setAttributes, 'activeColor') },
							{ label: __('Cart background', 'shopapp-blocks'), value: attrs.cartBackground, onChange: setAttr(setAttributes, 'cartBackground') },
							{ label: __('Cart icon', 'shopapp-blocks'), value: attrs.cartColor, onChange: setAttr(setAttributes, 'cartColor') },
							{ label: __('Cart count background', 'shopapp-blocks'), value: attrs.countBackground, onChange: setAttr(setAttributes, 'countBackground') },
							{ label: __('Cart count text', 'shopapp-blocks'), value: attrs.countColor, onChange: setAttr(setAttributes, 'countColor') }
						]
					})
				),
				preview('shopapp/bottom-nav', attrs, 'shopapp-editor-preview')
			);
		},
		save: function () { return null; }
	});
})(window.wp, window.shopappBlocksEditorSettings);
