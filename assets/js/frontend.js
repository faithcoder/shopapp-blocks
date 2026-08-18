(function () {
	'use strict';

	var cart = { items: [], count: 0, is_empty: true };
	var activeProduct = null;
	var previousCartCount = null;
	var savedProductIds = [];
	var searchTimer = null;
	var searchController = null;
	var savedStorageKey = 'shopappSavedProducts';

	function setting(name, fallback) {
		var settings = window.shopappBlocksSettings || {};
		return typeof settings[name] === 'undefined' ? fallback : settings[name];
	}

	function translate(name, fallback) {
		var strings = setting('i18n', {});
		return strings && strings[name] ? strings[name] : fallback;
	}

	function money(value) {
		var symbol = window.shopappBlocksSettings && window.shopappBlocksSettings.currency ? window.shopappBlocksSettings.currency : '$';
		return symbol + Math.round(Number(value || 0)).toLocaleString();
	}

	function parseJson(value) {
		try {
			return JSON.parse(value || '{}');
		} catch (error) {
			return {};
		}
	}

	function parseProduct(card) {
		return parseJson(card ? card.getAttribute('data-shopapp-product') : '{}');
	}

	function getLocalSavedIds() {
		try {
			var stored = JSON.parse(window.localStorage.getItem(savedStorageKey) || '[]');
			return Array.isArray(stored) ? stored.map(Number).filter(Boolean).slice(0, 100) : [];
		} catch (error) {
			return [];
		}
	}

	function setSavedIds(ids) {
		savedProductIds = Array.from(new Set((ids || []).map(Number).filter(Boolean))).slice(0, 100);
		try {
			window.localStorage.setItem(savedStorageKey, JSON.stringify(savedProductIds));
		} catch (error) {}
		updateSavedButtons();
	}

	function updateSavedButtons() {
		document.querySelectorAll('[data-shopapp-save]').forEach(function (button) {
			var isSaved = savedProductIds.indexOf(Number(button.getAttribute('data-product-id'))) !== -1;
			button.classList.toggle('is-saved', isSaved);
			button.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
			button.setAttribute('aria-label', isSaved ? translate('removeFromSaved', 'Remove from saved') : translate('saveProduct', 'Save product'));
		});
	}

	function setHidden(el, hidden) {
		if (!el) {
			return;
		}
		if (hidden) {
			el.setAttribute('hidden', '');
			document.body.classList.remove('shopapp-sheet-open');
		} else {
			el.removeAttribute('hidden');
			document.body.classList.add('shopapp-sheet-open');
		}
	}

	function cartRequest(action, data, nonce) {
		if (!window.shopappBlocksSettings || !window.shopappBlocksSettings.ajaxUrl) {
			return Promise.reject(new Error('WooCommerce cart is unavailable.'));
		}

		var body = new window.URLSearchParams();
		body.set('action', action);
		body.set('nonce', nonce || window.shopappBlocksSettings.cartNonce || '');
		Object.keys(data || {}).forEach(function (key) {
			body.set(key, String(data[key]));
		});

		return window.fetch(window.shopappBlocksSettings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (response) {
			return response.json();
		}).then(function (result) {
			if (!result || !result.success) {
				throw new Error(result && result.data && result.data.message ? result.data.message : 'Cart could not be updated.');
			}
			return result.data || {};
		});
	}

	function navigationRequest(action, data) {
		return cartRequest(action, data || {}, setting('navigationNonce', ''));
	}

	function syncSavedProducts(mode) {
		return navigationRequest('shopapp_sync_saved_products', {
			product_ids: savedProductIds.join(','),
			mode: mode || 'merge'
		}).then(function (data) {
			setSavedIds(data.ids || []);
			return data;
		});
	}

	function toggleSavedProduct(productId) {
		var id = Number(productId);
		if (!id) {
			return;
		}
		if (savedProductIds.indexOf(id) === -1) {
			setSavedIds(savedProductIds.concat([id]));
		} else {
			setSavedIds(savedProductIds.filter(function (savedId) { return savedId !== id; }));
		}
		syncSavedProducts('replace').then(function (data) {
			var sheet = document.querySelector('[data-shopapp-nav-sheet]');
			if (sheet && !sheet.hidden && sheet.getAttribute('data-shopapp-panel') === 'saved') {
				renderSavedPanel(sheet.querySelector('[data-shopapp-nav-content]'), data.products || []);
			}
		}).catch(showNavigationError);
	}

	function addWooProduct(product, qty) {
		if (!window.shopappBlocksSettings || !window.shopappBlocksSettings.ajaxUrl || !product || !/^\d+$/.test(String(product.id))) {
			return Promise.reject(new Error('This product cannot be added to the WooCommerce cart.'));
		}

		return cartRequest(
			'shopapp_add_to_cart',
			{ product_id: product.id, quantity: qty || 1 },
			window.shopappBlocksSettings.addToCartNonce || ''
		).then(function (data) {
			syncCart(data.cart || {});
			return data;
		});
	}

	function updateCount() {
		var count = Number(cart.count || 0);
		document.querySelectorAll('.shopapp-bag-count').forEach(function (node) {
			node.textContent = String(count);
		});
		if (previousCartCount !== null && count > previousCartCount) {
			document.querySelectorAll('.shopapp-bottom-nav[data-shopapp-buzz="true"] .shopapp-bag-trigger').forEach(function (button) {
				button.classList.remove('is-buzzing');
				void button.offsetWidth;
				button.classList.add('is-buzzing');
			});
		}
		previousCartCount = count;
	}

	function addLine(product, qty) {
		if (!product || !product.id) {
			showCartError(new Error('Product is unavailable.'));
			return Promise.resolve(false);
		}
		return addWooProduct(product, qty || 1).catch(function (error) {
			showCartError(error);
			return false;
		});
	}

	function changeQty(key, quantity) {
		cartRequest('shopapp_update_cart_item', { key: key, quantity: Math.max(0, quantity) }).then(function (data) {
			syncCart(data.cart || {});
		}).catch(showCartError);
	}

	function removeLine(key) {
		cartRequest('shopapp_remove_cart_item', { key: key }).then(function (data) {
			syncCart(data.cart || {});
		}).catch(showCartError);
	}

	function showCartError(error) {
		document.querySelectorAll('[data-shopapp-coupon-status]').forEach(function (node) {
			node.textContent = error && error.message ? error.message : 'Cart could not be updated.';
		});
	}

	function syncCart(nextCart) {
		cart = nextCart && nextCart.items ? nextCart : { items: [], count: 0, is_empty: true };
		updateCount();
		renderCheckout();
	}

	function loadCart() {
		return cartRequest('shopapp_get_cart', {}).then(function (data) {
			syncCart(data.cart || {});
			return data.cart || {};
		}).catch(showCartError);
	}

	function setHtml(selector, value) {
		document.querySelectorAll(selector).forEach(function (node) {
			node.innerHTML = value || '';
		});
	}

	function renderCheckout() {
		var lines = cart.items || [];

		document.querySelectorAll('[data-shopapp-lines]').forEach(function (checkoutLines) {
			checkoutLines.innerHTML = '';
			if (!lines.length) {
				var empty = document.createElement('li');
				empty.className = 'shopapp-checkout-empty';
				empty.textContent = 'Your bag is empty.';
				checkoutLines.appendChild(empty);
			}
			lines.forEach(function (line) {
				var item = document.createElement('li');
				item.className = 'shopapp-checkout-line';
				item.innerHTML =
					'<img alt="">' +
					'<div><h3></h3><p></p><span class="shopapp-checkout-line__price"></span></div>' +
					'<div class="shopapp-qty">' +
					'<button type="button" data-shopapp-qty="-1" aria-label="Decrease">-</button>' +
					'<strong>' + Number(line.quantity || 0) + '</strong>' +
					'<button type="button" data-shopapp-qty="1" aria-label="Increase">+</button>' +
					'<button type="button" data-shopapp-remove aria-label="Remove">&times;</button>' +
					'</div>';
				item.querySelector('img').src = line.image || '';
				item.querySelector('h3').textContent = line.name || '';
				item.querySelector('p').textContent = line.variation || '';
				item.querySelector('.shopapp-checkout-line__price').innerHTML = line.line_price_html || '';
				item.querySelector('[data-shopapp-qty="-1"]').addEventListener('click', function () {
					changeQty(line.key, Number(line.quantity || 0) - 1);
				});
				item.querySelector('[data-shopapp-qty="1"]').addEventListener('click', function () {
					changeQty(line.key, Number(line.quantity || 0) + 1);
				});
				item.querySelector('[data-shopapp-remove]').addEventListener('click', function () {
					removeLine(line.key);
				});
				checkoutLines.appendChild(item);
			});
		});

		setHtml('[data-shopapp-subtotal]', cart.subtotal_html);
		setHtml('[data-shopapp-discount]', cart.discount_html);
		setHtml('[data-shopapp-shipping]', cart.shipping_html);
		setHtml('[data-shopapp-tax]', cart.tax_html);
		setHtml('[data-shopapp-total]', cart.total_html);
		document.querySelectorAll('[data-shopapp-discount-row]').forEach(function (node) { node.hidden = !cart.show_discount; });
		document.querySelectorAll('[data-shopapp-tax-row]').forEach(function (node) { node.hidden = !cart.show_tax; });
		document.querySelectorAll('[data-shopapp-checkout-link]').forEach(function (node) {
			node.href = cart.checkout_url || (window.shopappBlocksSettings && window.shopappBlocksSettings.checkoutUrl) || '#';
			node.classList.toggle('is-disabled', !!cart.is_empty);
			node.setAttribute('aria-disabled', cart.is_empty ? 'true' : 'false');
		});
		document.querySelectorAll('[data-shopapp-cart-link]').forEach(function (node) {
			node.href = cart.cart_url || (window.shopappBlocksSettings && window.shopappBlocksSettings.cartUrl) || '#';
		});
	}

	function openProduct(product) {
		activeProduct = product;
		var productSheet = document.querySelector('[data-shopapp-product-sheet]');
		if (!productSheet || !product) {
			return;
		}
		productSheet.querySelector('#shopapp-product-title').textContent = product.name || '';
		productSheet.querySelector('.shopapp-sheet__image').src = product.image || '';
		productSheet.querySelector('.shopapp-sheet__image').alt = product.name || '';
		productSheet.querySelector('.shopapp-sheet__tagline').textContent = product.tagline || '';
		productSheet.querySelector('.shopapp-sheet__price').innerHTML = product.price_html || money(product.price);
		productSheet.querySelector('.shopapp-sheet__rating').innerHTML = '<span class="shopapp-star-text">*</span> ' + (product.rating || '4.8') + ' - 214 reviews';
		var colorWrap = productSheet.querySelector('.shopapp-sheet__colors');
		colorWrap.innerHTML = '';
		(product.colors || ['Standard']).forEach(function (color, index) {
			var button = document.createElement('button');
			button.type = 'button';
			button.className = 'shopapp-color-pill' + (index === 0 ? ' is-active' : '');
			button.textContent = color;
			button.addEventListener('click', function () {
				colorWrap.querySelectorAll('.shopapp-color-pill').forEach(function (pill) { pill.classList.remove('is-active'); });
				button.classList.add('is-active');
			});
			colorWrap.appendChild(button);
		});
		setHidden(productSheet, false);
	}

	function createResultItem(product, savedContext) {
		var item = document.createElement('article');
		var media = document.createElement('a');
		var image = document.createElement('img');
		var body = document.createElement('div');
		var name = document.createElement('h3');
		var meta = document.createElement('p');
		var actions = document.createElement('div');
		var save = document.createElement('button');
		var productId = Number(product.id || 0);

		item.className = 'shopapp-nav-product';
		media.className = 'shopapp-nav-product__media';
		media.href = product.permalink || '#';
		image.src = product.image || (product.images && product.images[0] ? product.images[0].thumbnail || product.images[0].src : '');
		image.alt = product.name || '';
		media.appendChild(image);
		body.className = 'shopapp-nav-product__body';
		name.textContent = product.name || '';
		meta.className = 'shopapp-nav-product__meta';
		meta.textContent = product.price_text || formatStorePrice(product);
		body.appendChild(name);
		body.appendChild(meta);
		actions.className = 'shopapp-nav-product__actions';
		save.type = 'button';
		save.className = 'shopapp-nav-product__save';
		save.setAttribute('data-shopapp-save', '');
		save.setAttribute('data-product-id', String(productId));
		save.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"></path></svg>';
		actions.appendChild(save);

		if (product.type === 'simple' || !product.type) {
			var add = document.createElement('button');
			add.type = 'button';
			add.className = 'shopapp-nav-product__add';
			add.textContent = translate('addToBag', 'Add to bag');
			add.addEventListener('click', function () {
				addLine(normalizeStoreProduct(product), 1);
			});
			actions.appendChild(add);
		} else {
			var view = document.createElement('a');
			view.className = 'shopapp-nav-product__view';
			view.href = product.permalink || '#';
			view.textContent = translate('viewOptions', 'View options');
			actions.appendChild(view);
		}

		body.appendChild(actions);
		item.appendChild(media);
		item.appendChild(body);
		return item;
	}

	function formatStorePrice(product) {
		var prices = product && product.prices ? product.prices : {};
		var minorUnit = Number(prices.currency_minor_unit || 0);
		var amount = Number(prices.price || 0) / Math.pow(10, minorUnit);
		try {
			return new Intl.NumberFormat(document.documentElement.lang || undefined, {
				style: 'currency',
				currency: prices.currency_code || 'USD'
			}).format(amount);
		} catch (error) {
			return (prices.currency_symbol || setting('currency', '$')) + amount.toFixed(minorUnit);
		}
	}

	function normalizeStoreProduct(product) {
		var prices = product.prices || {};
		var minorUnit = Number(prices.currency_minor_unit || 0);
		return {
			id: product.id,
			name: product.name,
			image: product.images && product.images[0] ? product.images[0].src : '',
			price: Number(prices.price || 0) / Math.pow(10, minorUnit),
			price_text: formatStorePrice(product),
			price_html: formatStorePrice(product),
			permalink: product.permalink,
			type: product.type,
			colors: []
		};
	}

	function renderProductList(container, products, savedContext, append) {
		if (!append) {
			container.innerHTML = '';
		}
		if (!products.length && !append) {
			var empty = document.createElement('p');
			empty.className = 'shopapp-nav-empty';
			empty.textContent = savedContext ? translate('noSaved', 'You have not saved any products yet.') : translate('noResults', 'No products matched your search.');
			container.appendChild(empty);
			return;
		}
		products.forEach(function (product) {
			container.appendChild(createResultItem(product, savedContext));
		});
		updateSavedButtons();
	}

	function fetchSearchResults(query, results, page) {
		page = page || 1;
		if (searchController) {
			searchController.abort();
		}
		searchController = 'AbortController' in window ? new AbortController() : null;
		if (page === 1) {
			results.innerHTML = '<p class="shopapp-nav-status">' + translate('loading', 'Loading...') + '</p>';
		} else {
			var currentMore = results.querySelector('[data-shopapp-search-more]');
			if (currentMore) {
				currentMore.disabled = true;
				currentMore.textContent = translate('loading', 'Loading...');
			}
		}
		var url = new URL(setting('productsApiUrl', '/wp-json/wc/store/v1/products'));
		url.searchParams.set('search', query);
		url.searchParams.set('per_page', '8');
		url.searchParams.set('page', String(page));
		url.searchParams.set('catalog_visibility', 'search');
		window.fetch(url.toString(), {
			credentials: 'same-origin',
			signal: searchController ? searchController.signal : undefined
		}).then(function (response) {
			if (!response.ok) {
				throw new Error(translate('searchError', 'Search could not be loaded.'));
			}
			return response.json().then(function (products) {
				return {
					products: products,
					totalPages: Number(response.headers.get('X-WP-TotalPages') || 1)
				};
			});
		}).then(function (responseData) {
			var previousMore = results.querySelector('[data-shopapp-search-more]');
			if (previousMore) {
				previousMore.remove();
			}
			renderProductList(results, Array.isArray(responseData.products) ? responseData.products : [], false, page > 1);
			if (page < responseData.totalPages) {
				var more = document.createElement('button');
				more.type = 'button';
				more.className = 'shopapp-nav-load-more';
				more.setAttribute('data-shopapp-search-more', '');
				more.textContent = translate('loadMore', 'Load more');
				more.addEventListener('click', function () {
					fetchSearchResults(query, results, page + 1);
				});
				results.appendChild(more);
			}
		}).catch(function (error) {
			if (error.name !== 'AbortError') {
				showNavigationError(error, results);
			}
		});
	}

	function renderSearchPanel(content) {
		content.innerHTML = '<form class="shopapp-nav-search" role="search"><label class="screen-reader-text" for="shopapp-nav-search-input"></label><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg><input id="shopapp-nav-search-input" type="search" autocomplete="off"><button type="submit"></button></form><div class="shopapp-nav-products" data-shopapp-search-results><p class="shopapp-nav-status"></p></div>';
		var form = content.querySelector('.shopapp-nav-search');
		var input = form.querySelector('input');
		var results = content.querySelector('[data-shopapp-search-results]');
		input.placeholder = translate('searchProducts', 'Search products');
		form.querySelector('label').textContent = translate('searchProducts', 'Search products');
		form.querySelector('button').textContent = translate('search', 'Search');
		results.querySelector('.shopapp-nav-status').textContent = translate('searchPrompt', 'Enter at least two characters to search products.');
		form.addEventListener('submit', function (event) {
			event.preventDefault();
			if (input.value.trim().length >= 2) {
				fetchSearchResults(input.value.trim(), results, 1);
			}
		});
		input.addEventListener('input', function () {
			window.clearTimeout(searchTimer);
			if (input.value.trim().length < 2) {
				results.innerHTML = '<p class="shopapp-nav-status"></p>';
				results.querySelector('.shopapp-nav-status').textContent = translate('searchPrompt', 'Enter at least two characters to search products.');
				return;
			}
			searchTimer = window.setTimeout(function () {
				fetchSearchResults(input.value.trim(), results, 1);
			}, 300);
		});
		window.setTimeout(function () { input.focus(); }, 50);
	}

	function renderSavedPanel(content, products) {
		content.innerHTML = '<div class="shopapp-nav-products" data-shopapp-saved-results></div><p class="shopapp-nav-helper"></p>';
		content.querySelector('.shopapp-nav-helper').textContent = translate('savedHelper', 'Saved products sync to your account when you sign in.');
		renderProductList(content.querySelector('[data-shopapp-saved-results]'), products || [], true);
	}

	function loadSavedPanel(content) {
		content.innerHTML = '<p class="shopapp-nav-status">' + translate('loading', 'Loading...') + '</p>';
		syncSavedProducts('merge').then(function (data) {
			renderSavedPanel(content, data.products || []);
		}).catch(function (error) {
			showNavigationError(error, content);
		});
	}

	function appendAccountLink(container, label, url) {
		var link = document.createElement('a');
		link.href = url || '#';
		link.textContent = label;
		container.appendChild(link);
	}

	function renderAccountPanel(content, account) {
		content.innerHTML = '';
		if (!account.isLoggedIn) {
			var guest = document.createElement('div');
			guest.className = 'shopapp-account-guest';
			guest.innerHTML = '<p></p>';
			guest.querySelector('p').textContent = translate('signInPrompt', 'Sign in to view orders, addresses, downloads, and saved products.');
			var guestActions = document.createElement('div');
			guestActions.className = 'shopapp-account-actions';
			appendAccountLink(guestActions, translate('signIn', 'Sign in or create account'), account.loginUrl);
			appendAccountLink(guestActions, translate('lostPassword', 'Lost password'), account.lostPasswordUrl);
			guest.appendChild(guestActions);
			content.appendChild(guest);
			return;
		}

		var profile = document.createElement('div');
		profile.className = 'shopapp-account-profile';
		var avatar = document.createElement('img');
		avatar.src = account.avatar || '';
		avatar.alt = '';
		var identity = document.createElement('div');
		var heading = document.createElement('h3');
		var email = document.createElement('p');
		heading.textContent = account.name || '';
		email.textContent = account.email || '';
		identity.appendChild(heading);
		identity.appendChild(email);
		profile.appendChild(avatar);
		profile.appendChild(identity);
		content.appendChild(profile);

		var links = document.createElement('nav');
		links.className = 'shopapp-account-links';
		links.setAttribute('aria-label', 'Account');
		appendAccountLink(links, translate('orders', 'Orders'), account.links.orders);
		appendAccountLink(links, translate('downloads', 'Downloads'), account.links.downloads);
		appendAccountLink(links, translate('addresses', 'Addresses'), account.links.addresses);
		appendAccountLink(links, translate('accountDetails', 'Account details'), account.links.details);
		appendAccountLink(links, translate('paymentMethods', 'Payment methods'), account.links.payments);
		content.appendChild(links);

		var orderSection = document.createElement('section');
		orderSection.className = 'shopapp-account-orders';
		orderSection.innerHTML = '<div class="shopapp-account-orders__heading"><h3></h3></div>';
		orderSection.querySelector('h3').textContent = translate('recentOrders', 'Recent orders');
		if (!(account.orders || []).length) {
			var noOrders = document.createElement('p');
			noOrders.className = 'shopapp-nav-empty';
			noOrders.textContent = translate('noOrders', 'No orders yet.');
			orderSection.appendChild(noOrders);
		}
		(account.orders || []).forEach(function (order) {
			var orderLink = document.createElement('a');
			orderLink.className = 'shopapp-account-order';
			orderLink.href = order.url || '#';
			var summary = document.createElement('span');
			var total = document.createElement('strong');
			summary.textContent = '#' + order.number + ' - ' + order.status + ' - ' + order.date;
			total.innerHTML = order.total || '';
			orderLink.appendChild(summary);
			orderLink.appendChild(total);
			orderSection.appendChild(orderLink);
		});
		content.appendChild(orderSection);
		var footer = document.createElement('div');
		footer.className = 'shopapp-account-footer';
		appendAccountLink(footer, translate('openAccount', 'Open My Account'), account.links.account);
		appendAccountLink(footer, translate('signOut', 'Sign out'), account.links.logout);
		content.appendChild(footer);
	}

	function loadAccountPanel(content) {
		content.innerHTML = '<p class="shopapp-nav-status">' + translate('loading', 'Loading...') + '</p>';
		navigationRequest('shopapp_get_account_summary', {}).then(function (account) {
			renderAccountPanel(content, account);
		}).catch(function (error) {
			showNavigationError(error, content);
		});
	}

	function showNavigationError(error, container) {
		var target = container || document.querySelector('[data-shopapp-nav-content]');
		if (target) {
			target.innerHTML = '';
			var message = document.createElement('p');
			message.className = 'shopapp-nav-empty';
			message.textContent = error && error.message ? error.message : translate('panelError', 'This panel could not be loaded.');
			target.appendChild(message);
		}
	}

	function openNavPopup(type) {
		var sheet = document.querySelector('[data-shopapp-nav-sheet]');
		if (!sheet) {
			return;
		}
		var title = sheet.querySelector('[data-shopapp-nav-title]');
		var content = sheet.querySelector('[data-shopapp-nav-content]');
		var labels = {
			shop: 'Shop',
			search: 'Search',
			saved: 'Saved',
			you: 'You'
		};
		title.textContent = labels[type] || 'Shop';
		sheet.setAttribute('data-shopapp-panel', type);
		if (type === 'search') {
			renderSearchPanel(content);
		} else if (type === 'saved') {
			loadSavedPanel(content);
		} else if (type === 'you') {
			loadAccountPanel(content);
		} else {
			content.innerHTML = '<button class="shopapp-button shopapp-button--ember" type="button" data-shopapp-browse-products></button>';
			content.querySelector('[data-shopapp-browse-products]').textContent = translate('browseProducts', 'Browse products');
		}
		setHidden(sheet, false);
	}

	function requestProductPage(block, page, append, control) {
		var grid = block ? block.querySelector('[data-shopapp-product-grid]') : null;
		if (!block || !grid || !window.shopappBlocksSettings) {
			return;
		}
		var body = new window.URLSearchParams();
		body.set('action', 'shopapp_load_products');
		body.set('nonce', window.shopappBlocksSettings.loadProductsNonce || '');
		body.set('page', String(page));
		body.set('query', block.getAttribute('data-shopapp-query') || '{}');
		if (control && 'disabled' in control) {
			control.disabled = true;
		}
		block.setAttribute('aria-busy', 'true');
		window.fetch(window.shopappBlocksSettings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (response) {
			return response.json();
		}).then(function (result) {
			if (result && result.success && result.data && result.data.html) {
				if (append) {
					grid.insertAdjacentHTML('beforeend', result.data.html);
				} else {
					grid.innerHTML = result.data.html;
				}
				block.setAttribute('data-shopapp-page', String(page));
				updateSavedButtons();
			}
		}).finally(function () {
			if (control && 'disabled' in control) {
				control.disabled = false;
			}
			block.removeAttribute('aria-busy');
		});
	}

	function loadMore(button) {
		var block = button.closest('.shopapp-product-grid-block');
		var page = parseInt(block ? block.getAttribute('data-shopapp-page') || '1' : '1', 10) + 1;
		var maxPages = parseInt(block ? block.getAttribute('data-shopapp-max-pages') || '1' : '1', 10);
		requestProductPage(block, page, true, button);
		if (page >= maxPages) {
			button.remove();
		}
	}

	function getPaginationPage(link) {
		try {
			return parseInt(new URL(link.href).searchParams.get('product-page') || '1', 10);
		} catch (error) {
			return parseInt(link.textContent || '1', 10);
		}
	}

	function updatePaginationUrl(page) {
		if (!window.history || !window.history.pushState) {
			return;
		}
		var url = new URL(window.location.href);
		if (page > 1) {
			url.searchParams.set('product-page', String(page));
		} else {
			url.searchParams.delete('product-page');
		}
		window.history.pushState({ shopappProductPage: page }, '', url.toString());
	}

	function paginateProducts(link) {
		var block = link.closest('.shopapp-product-grid-block');
		var page = Math.max(1, getPaginationPage(link) || 1);
		requestProductPage(block, page, false, link);
		if (block) {
			block.querySelectorAll('.shopapp-pagination a').forEach(function (item) {
				var itemPage = Math.max(1, getPaginationPage(item) || 1);
				item.classList.toggle('is-active', itemPage === page);
				if (itemPage === page) {
					item.setAttribute('aria-current', 'page');
				} else {
					item.removeAttribute('aria-current');
				}
			});
		}
		updatePaginationUrl(page);
	}

	document.addEventListener('click', function (event) {
		var target = event.target;
		var card = target.closest ? target.closest('.shopapp-product-card') : null;

		if (target.closest('[data-shopapp-open-product]') && card) {
			openProduct(parseProduct(card));
		}
		if (target.closest('[data-shopapp-add]') && card) {
			addLine(parseProduct(card), 1);
		}
		var saveButton = target.closest('[data-shopapp-save]');
		if (saveButton) {
			event.preventDefault();
			toggleSavedProduct(saveButton.getAttribute('data-product-id'));
		}
		if (target.closest('[data-shopapp-open-checkout]')) {
			setHidden(document.querySelector('[data-shopapp-checkout]'), false);
			loadCart();
		}
		if (target.closest('[data-shopapp-close-sheet]')) {
			setHidden(document.querySelector('[data-shopapp-product-sheet]'), true);
		}
		if (target.closest('[data-shopapp-close-checkout]')) {
			setHidden(document.querySelector('[data-shopapp-checkout]'), true);
		}
		if (target.closest('[data-shopapp-close-nav]')) {
			setHidden(document.querySelector('[data-shopapp-nav-sheet]'), true);
		}
		if (target.closest('[data-shopapp-sheet-add]')) {
			addLine(activeProduct, 1);
		}
		if (target.closest('[data-shopapp-sheet-buy]')) {
			addLine(activeProduct, 1).then(function (added) {
				if (added === false) {
					return;
				}
				setHidden(document.querySelector('[data-shopapp-product-sheet]'), true);
				setHidden(document.querySelector('[data-shopapp-checkout]'), false);
			});
		}
		if (target.closest('[data-shopapp-continue]')) {
			setHidden(document.querySelector('[data-shopapp-checkout]'), true);
		}
		if (target.closest('[data-shopapp-browse-products]')) {
			setHidden(document.querySelector('[data-shopapp-nav-sheet]'), true);
			var productGrid = document.querySelector('[data-shopapp-product-grid]');
			if (productGrid) {
				productGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		}
		var checkoutLink = target.closest('[data-shopapp-checkout-link]');
		if (checkoutLink && checkoutLink.getAttribute('aria-disabled') === 'true') {
			event.preventDefault();
		}
		var navButton = target.closest('[data-shopapp-nav-popup]');
		if (navButton) {
			openNavPopup(navButton.getAttribute('data-shopapp-nav-popup'));
		}
		var more = target.closest('[data-shopapp-load-more]');
		if (more) {
			loadMore(more);
		}
		var paginationLink = target.closest('.shopapp-pagination a');
		if (paginationLink) {
			event.preventDefault();
			paginateProducts(paginationLink);
		}
	});

	document.addEventListener('submit', function (event) {
		var form = event.target.closest ? event.target.closest('[data-shopapp-coupon-form]') : null;
		if (!form) {
			return;
		}
		event.preventDefault();
		var input = form.querySelector('[name="coupon"]');
		var button = form.querySelector('button[type="submit"]');
		var status = form.querySelector('[data-shopapp-coupon-status]');
		button.disabled = true;
		status.textContent = '';
		cartRequest('shopapp_apply_coupon', { coupon: input ? input.value : '' }).then(function (data) {
			syncCart(data.cart || {});
			status.textContent = data.message || 'Coupon applied.';
			if (input) {
				input.value = '';
			}
		}).catch(function (error) {
			status.textContent = error && error.message ? error.message : 'Coupon could not be applied.';
		}).finally(function () {
			button.disabled = false;
		});
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			setHidden(document.querySelector('[data-shopapp-product-sheet]'), true);
			setHidden(document.querySelector('[data-shopapp-checkout]'), true);
			setHidden(document.querySelector('[data-shopapp-nav-sheet]'), true);
		}
	});

	renderCheckout();
	loadCart();
	setSavedIds(getLocalSavedIds());
	syncSavedProducts('merge').catch(function () {});
})();
