/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import AdminPage from './components/admin-page/admin-page';

/**
 * Filter for adding our page to the list of WooCommerce Admin pages
 */
addFilter(
	'woocommerce_admin_pages_list',
	'arraycodes-order-notifications-woocommerce',
	( pages ) => {
		pages.push( {
			breadcrumbs: [
				'',
				wcSettings.woocommerceTranslation,
				__(
					'ArrayCodes Order Notifications',
					'arraycodes-order-notifications-woocommerce'
				),
			],
			capability: 'manage_options',
			container: AdminPage,
			path: '/arraycodes-order-notifications-woocommerce',
			wpOpenMenu: 'toplevel_page_woocommerce',
			title: __(
				'ArrayCodes Order Notifications',
				'arraycodes-order-notifications-woocommerce'
			),
			navArgs: {
				id: 'arraycodes-order-notifications-woocommerce',
			},
		} );

		return pages;
	}
);
