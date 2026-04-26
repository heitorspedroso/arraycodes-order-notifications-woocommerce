/**
 * External dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { createReduxStore, register } from '@wordpress/data';

const DEFAULT_STATE = {
	whatsapp_api_log: false,
	whatsapp_api_token: '',
	whatsapp_api_phone_number: '',
	whatsapp_api_account_id: '',
	whatsapp_api_phone_number_to: '',
	whatsapp_api_checked_input: false,
	whatsapp_api_webhook_callback_url: '',
	whatsapp_api_webhook_token: '',
	whatsapp_template_new_order_id: '',
	whatsapp_template_new_order_status: '',
	whatsapp_template_new_order_category: '',
	whatsapp_template_new_order_name: '',
	whatsapp_template_new_order_language: '',
	whatsapp_template_new_order_header: '',
	whatsapp_template_new_order_footer: '',
	whatsapp_template_new_order_body: '',
	whatsapp_template_new_order_customer_id: '',
	whatsapp_template_new_order_customer_status: '',
	whatsapp_template_new_order_customer_category: '',
	whatsapp_template_new_order_customer_name: '',
	whatsapp_template_new_order_customer_language: '',
	whatsapp_template_new_order_customer_header: '',
	whatsapp_template_new_order_customer_header_type: '',
	whatsapp_template_new_order_customer_footer: '',
	whatsapp_template_new_order_customer_body: '',
	whatsapp_received_messages: [],
	whatsapp_total_received_messages: 0,
	whatsapp_received_messages_by_id: [],
};

const actions = {
	initSettings( settings ) {
		// console.log('actions initSettings',settings);
		return {
			type: 'STATE_FROM_DATABASE',
			payload: {
				...settings,
			},
		};
	},
	*saveSettings( settings, currentState ) {
		const combinedState = {
			...DEFAULT_STATE,
			...currentState,
			...settings,
		};
		try {
			yield actions.saveSettingsToDatabase( combinedState );
			return {
				type: 'SAVE_SETTINGS',
				payload: combinedState,
			};
		} catch ( error ) {
			throw error;
		}
	},
	saveSettingsToDatabase( state ) {
		return {
			type: 'SAVE_SETTINGS_TO_DATABASE',
			payload: state,
		};
	},
	saveOrderSeller( orderSeller ) {
		return {
			type: 'SAVE_ORDER_SELLER',
			payload: {
				...orderSeller,
			},
		};
	},
	saveOrderCustomer( orderCustomer ) {
		return {
			type: 'SAVE_ORDER_CUSTOMER',
			payload: {
				...orderCustomer,
			},
		};
	},
	saveOrderStatusCustomer( orderCustomer ) {
		return {
			type: 'SAVE_ORDER_STATUS_CUSTOMER',
			payload: {
				...orderCustomer,
			},
		};
	},
	saveAbandonedCartCustomer( abandonedCartCustomer ) {
		return {
			type: 'SAVE_ABANDONED_CART_CUSTOMER',
			payload: {
				...abandonedCartCustomer,
			},
		};
	},
	saveUnpaidOrderCustomer( unpaidOrderCustomer ) {
		return {
			type: 'SAVE_UNPAID_ORDER_CUSTOMER',
			payload: {
				...unpaidOrderCustomer,
			},
		};
	},
	saveOrderDetailsCustomer( orderDetailsCustomer ) {
		return {
			type: 'SAVE_ORDER_DETAILS_CUSTOMER',
			payload: {
				...orderDetailsCustomer,
			},
		};
	},
	saveMessages( messages ) {
		return {
			type: 'SAVE_MESSAGES',
			payload: {
				...messages,
			},
		};
	},
	saveMessagesById( messages ) {
		return {
			type: 'SAVE_MESSAGES_BY_ID',
			payload: {
				...messages,
			},
		};
	},
	saveNotifyMe( products ) {
		return {
			type: 'SAVE_NOTIFY_ME',
			payload: {
				...products,
			},
		};
	},
	saveNotifyMeById( products ) {
		return {
			type: 'SAVE_NOTIFY_ME_BY_ID',
			payload: {
				...products,
			},
		};
	},
	saveOutOfStockCustomer( outOfStockCustomer ) {
		return {
			type: 'SAVE_OUT_OF_STOCK_CUSTOMER',
			payload: {
				...outOfStockCustomer,
			},
		};
	},
	saveBackInStockCustomer( backInStockCustomer ) {
		return {
			type: 'SAVE_BACK_IN_STOCK_CUSTOMER',
			payload: {
				...backInStockCustomer,
			},
		};
	},
	saveReviewNotificationCustomer( reviewNotificationCustomer ) {
		return {
			type: 'SAVE_REVIEW_NOTIFICATION_CUSTOMER',
			payload: {
				...reviewNotificationCustomer,
			},
		};
	},
	saveWebhookCallbackUrl( webhookCallbackUrl ) {
		return {
			type: 'SAVE_WEBHOOK_CALLBACK_URL',
			payload: {
				...webhookCallbackUrl,
			},
		};
	},
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case 'STATE_FROM_DATABASE':
			// console.log('reducer STATE_FROM_DATABASE',action.payload);
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_SETTINGS':
			// console.log('reducer SAVE_SETTINGS',state);
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_SETTINGS_TO_DATABASE':
			// console.log('reducer SAVE_SETTINGS_TO_DATABASE', action.payload);
			return state;
		case 'SAVE_ORDER_SELLER':
			// console.log( 'reducer SAVE_ORDER_SELLER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_ORDER_CUSTOMER':
			// console.log( 'reducer SAVE_ORDER_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_ORDER_STATUS_CUSTOMER':
			// console.log( 'reducer SAVE_ORDER_STATUS_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_ABANDONED_CART_CUSTOMER':
			// console.log( 'reducer SAVE_ABANDONED_CART_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_UNPAID_ORDER_CUSTOMER':
			// console.log( 'reducer SAVE_UNPAID_ORDER_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_ORDER_DETAILS_CUSTOMER':
			// console.log( 'reducer SAVE_ORDER_DETAILS_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_MESSAGES':
			// console.log( 'reducer SAVE_MESSAGES', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_MESSAGES_BY_ID':
			// console.log( 'reducer SAVE_MESSAGES_BY_ID', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_NOTIFY_ME':
			// console.log( 'reducer SAVE_NOTIFY_ME', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_NOTIFY_ME_BY_ID':
			// console.log( 'reducer SAVE_NOTIFY_ME_BY_ID', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_OUT_OF_STOCK_CUSTOMER':
			// console.log( 'reducer SAVE_OUT_OF_STOCK_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_BACK_IN_STOCK_CUSTOMER':
			// console.log( 'reducer SAVE_BACK_IN_STOCK_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_REVIEW_NOTIFICATION_CUSTOMER':
			// console.log( 'reducer SAVE_REVIEW_NOTIFICATION_CUSTOMER', state );
			return {
				...state,
				...action.payload,
			};
		case 'SAVE_WEBHOOK_CALLBACK_URL':
			// console.log( 'reducer SAVE_WEBHOOK_CALLBACK_URL', state );
			return {
				...state,
				...action.payload,
			};
		default:
			return state;
	}
};

const selectors = {
	getState( state ) {
		return {
			...DEFAULT_STATE,
			...state,
		};
	},
	getSettings( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getOrderSeller( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getOrderCustomer( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getAbandonedCart( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getUnpaidOrder( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getOrderDetailsCustomer( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getMessages( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getMessagesById( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getProductsNotifyMe( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getProductsNotifyMeById( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getOutOfStock( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getBackInStock( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getReviewNotification( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getOrderStatusCustomer( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
	getWebhookCallbackUrl( state ) {
		const { ...settings } = state;
		// console.log('selectors',settings);
		return settings;
	},
};

const resolvers = {
	getSettings() {
		return async ( { dispatch } ) => {
			const settings = await apiFetch( { path: '/wp/v2/settings' } );
			// console.log('resolvers getSettings',settings['arraycodes_on_fields_free']);
			dispatch.initSettings( settings.arraycodes_on_fields_free );
		};
	},
	getOrderSeller() {
		return async ( { dispatch } ) => {
			const orderSeller = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-order-seller',
			} );
			// console.log( 'resolvers getOrderSeller', orderSeller );
			if (
				orderSeller.message === 'ok' ||
				orderSeller.message.code === 100
			) {
				dispatch.saveOrderSeller( orderSeller.data );
			}
		};
	},
	getOrderCustomer() {
		return async ( { dispatch } ) => {
			const orderCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-order-customer',
			} );
			// console.log( 'resolvers getOrderCustomer', orderCustomer );
			if (
				orderCustomer.message === 'ok' ||
				orderCustomer.message.code === 100
			) {
				dispatch.saveOrderCustomer( orderCustomer.data );
			}
		};
	},
	getOrderStatusCustomer() {
		return async ( { dispatch } ) => {
			const orderStatusCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-order-status-customer',
			} );
			// console.log( 'resolvers getOrderStatusCustomer', orderStatusCustomer );
			if ( orderStatusCustomer.message === 'ok' ) {
				dispatch.saveOrderStatusCustomer( orderStatusCustomer.data );
			}
		};
	},
	getAbandonedCart() {
		return async ( { dispatch } ) => {
			const abandonedCartCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-abandoned-cart-customer',
			} );
			// console.log( 'resolvers getAbandonedCart', abandonedCartCustomer );
			if (
				abandonedCartCustomer.message === 'ok' ||
				abandonedCartCustomer.message.code === 100
			) {
				dispatch.saveAbandonedCartCustomer(
					abandonedCartCustomer.data
				);
			}
		};
	},
	getOutOfStock() {
		return async ( { dispatch } ) => {
			const outOfStockCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-out-of-stock-customer',
			} );
			// console.log( 'resolvers getOutOfStock', outOfStockCustomer );
			if (
				outOfStockCustomer.message === 'ok' ||
				outOfStockCustomer.message.code === 100
			) {
				dispatch.saveOutOfStockCustomer( outOfStockCustomer.data );
			}
		};
	},
	getBackInStock() {
		return async ( { dispatch } ) => {
			const backInStockCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-back-in-stock-customer',
			} );
			// console.log( 'resolvers getBackInStock', backInStockCustomer );
			if (
				backInStockCustomer.message === 'ok' ||
				backInStockCustomer.message.code === 100
			) {
				dispatch.saveBackInStockCustomer( backInStockCustomer.data );
			}
		};
	},
	getReviewNotification() {
		return async ( { dispatch } ) => {
			const reviewNotificationCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-review-notification-customer',
			} );
			// console.log( 'resolvers getReviewNotification', reviewNotificationCustomer );
			if (
				reviewNotificationCustomer.message === 'ok' ||
				reviewNotificationCustomer.message.code === 100
			) {
				dispatch.saveReviewNotificationCustomer(
					reviewNotificationCustomer.data
				);
			}
		};
	},
	getUnpaidOrder() {
		return async ( { dispatch } ) => {
			const unpaidOrderCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-unpaid-order-customer',
			} );
			// console.log( 'resolvers getUnpaidOrder', unpaidOrderCustomer );
			if (
				unpaidOrderCustomer.message === 'ok' ||
				unpaidOrderCustomer.message.code === 100
			) {
				dispatch.saveUnpaidOrderCustomer( unpaidOrderCustomer.data );
			}
		};
	},
	getOrderDetailsCustomer() {
		return async ( { dispatch } ) => {
			const orderDetailsCustomer = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-order-details-customer',
			} );
			// console.log( 'resolvers getOrderDetailsCustomer', orderDetailsCustomer );
			if (
				orderDetailsCustomer.message === 'ok' ||
				orderDetailsCustomer.message.code === 100
			) {
				dispatch.saveOrderCustomer( orderDetailsCustomer.data );
			}
		};
	},
	getMessages( page, perPage, perPageKey ) {
		return async ( { dispatch } ) => {
			const messages = await apiFetch( {
				method: 'GET',
				path: `/arraycodes-order-notifications-woocommerce/v1/get-messages?page=${ page }&per_page=${ perPage }`,
			} );
			// console.log( 'resolvers getMessages', messages.result );
			if ( messages.result.messages ) {
				dispatch.saveMessages( {
					whatsapp_received_messages: messages.result.messages,
					whatsapp_total_received_messages:
						messages.result.total_messages,
				} );
			}
		};
	},
	getMessagesById( waId, perPageKey ) {
		return async ( { dispatch } ) => {
			const messages = await apiFetch( {
				method: 'GET',
				path: `/arraycodes-order-notifications-woocommerce/v1/get-messages-by-id?wa_id=${ waId }`,
			} );
			// console.log( 'resolvers getMessagesById', messages.result );
			if ( messages.result ) {
				dispatch.saveMessagesById( {
					whatsapp_received_messages_by_id: messages.result,
				} );
			}
		};
	},
	getProductsNotifyMe( page, perPage, perPageKey ) {
		return async ( { dispatch } ) => {
			const products_notify_me = await apiFetch( {
				method: 'GET',
				path: `/arraycodes-order-notifications-woocommerce/v1/get-products-notify-me?page=${ page }&per_page=${ perPage }`,
			} );
			// console.log( 'resolvers getProductsNotifyMe', products_notify_me.result );
			if ( products_notify_me.result.products_notify_me ) {
				dispatch.saveNotifyMe( {
					whatsapp_received_products_notify_me:
						products_notify_me.result.products_notify_me,
					whatsapp_total_received_products_notify_me:
						products_notify_me.result.total_products_notify_me,
				} );
			}
		};
	},
	getProductsNotifyMeById( productId, page, perPage, perPageKey ) {
		return async ( { dispatch } ) => {
			const response = await apiFetch( {
				method: 'GET',
				path: `/arraycodes-order-notifications-woocommerce/v1/get-products-notify-me-by-id
				?product_id=${ productId }
				&page=${ page }
				&per_page=${ perPage }`,
			} );

			if ( response?.items ) {
				dispatch.saveNotifyMeById( {
					whatsapp_received_products_notify_me_by_id: response.items,
					whatsapp_total_received_products_notify_me_by_id:
						response.total,
				} );
			}

			return response;
		};
	},
	getWebhookCallbackUrl() {
		return async ( { dispatch } ) => {
			const webhookCallbackUrl = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-webhook-callback-url',
			} );
			// console.log( 'resolvers getWebhookCallbackUrl', webhookCallbackUrl )
			if ( webhookCallbackUrl.message === 'ok' ) {
				dispatch.saveWebhookCallbackUrl( webhookCallbackUrl.data );
			}
		};
	},
};

const controls = {
	async SAVE_SETTINGS_TO_DATABASE( state ) {
		try {
			const settings = await apiFetch( { path: '/wp/v2/settings' } );

			if ( settings && settings.arraycodes_on_fields_free ) {
				await apiFetch( {
					path: '/wp/v2/settings',
					method: 'PUT',
					body: JSON.stringify( {
						arraycodes_on_fields_free: state.payload,
					} ),
				} );
				// console.log('State saved to the database', settings);
				return state;
			}
			// console.log('arraycodes_on_fields_free not found in settings');
			throw 'error';
		} catch ( error ) {
			// console.error('Error saving state to the database:', error);
			throw error;
		}
	},
};

const store = createReduxStore( 'shop-arraycodes-order-notifications-woocommerce', {
	reducer,
	actions,
	selectors,
	resolvers,
	controls,
} );

register( store );
