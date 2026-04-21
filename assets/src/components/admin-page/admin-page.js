/**
 * External dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import '../../scss/index.scss';
import '../../store';
import MyPanel from '../panel/my-panel';
import PageFormWhatsConnection from '../menu-whats-connection/form-whats-connection';
import WebhookWhatsConnection from '../menu-whats-connection/webhook-whats-connection';
import PageFormTemplateNewOrder from '../menu-message-templates/form-template-new-order-seller';
import PageFormTemplateNewOrderCustomer from '../menu-message-templates/form-template-new-order-customer';
import PageFormTemplateUpdateOrderStatusCustomer from '../menu-message-templates/form-template-update-order-status-customer';
import PageFormTemplateAbandonedCartCustomer from '../menu-message-templates/form-template-abandoned-cart-customer';
import PageFormTemplateUnpaidOrderCustomer from '../menu-message-templates/form-template-unpaid-order-customer';
import PageFormTemplateOrderDetailsCustomer from '../menu-message-templates/form-template-order-details-customer';
import PageFormAutoReplyReceivedMessages from '../menu-received-messages/form-auto-reply-received-messages';
import PageFormReceivedMessages from '../menu-received-messages/form-received-messages';
import PageStockNotifications from '../menu-stock-notifications/form-stock-notifications';
import PageFormTemplateOutOfStockCustomer from '../menu-stock-notifications/form-template-out-of-stock';
import PageFormTemplateBackInStockCustomer from '../menu-stock-notifications/form-template-back-in-stock';
import PageFormTemplateReviewNotificationCustomer from '../menu-review-notifications/form-template-review-notification';
import Coupon from '../menu-extra/coupon';
import PageHelp from '../menu-extra/help';
import About from '../menu-extra/about';

import ImageNotificationWithWhatsAppFree from '../../../img/logo-arraycodes-order-notifications-woocommerce.png';

const AdminPage = () => {
	const { createSuccessNotice } = useDispatch( noticesStore );

	const [ activeNavItem, setActiveNavItem ] = useState( 'item-1' );

	const handleNavItemChange = ( item ) => {
		setActiveNavItem( item );
	};

	useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getSettings()
	);

	useEffect( () => {
		const activeComponent =
			window.localStorage.getItem( 'activeComponent' );
		if ( activeComponent ) {
			handleNavItemChange( activeComponent );
			createSuccessNotice(
				__(
					'Data sent to WhatsApp Cloud API',
					'arraycodes-order-notifications-woocommerce'
				),
				{
					type: 'snackbar',
				}
			);
			window.localStorage.removeItem( 'activeComponent' );
		}
	}, [] );

	return (
		<div>
			<div className="wrap notifications-with-whatsapp">
				<div
					className={
						'content-sidebar-notifications-with-whatsapp-wrap'
					}
				>
					<div className={ 'logoNotificationWithWhatsAppFree' }>
						<img
							src={ ImageNotificationWithWhatsAppFree }
							width={ '100' }
							alt="NotificationWithWhatsAppFree"
						/>
					</div>
					<MyPanel
						onNavItemChange={ handleNavItemChange }
						activeNavItem={ activeNavItem }
					/>
				</div>
				<div
					className={
						'content-form-notifications-with-whatsapp-wrap'
					}
				>
					{ activeNavItem === 'item-1' && (
						<PageFormWhatsConnection />
					) }
					{ activeNavItem === 'item-2' && <WebhookWhatsConnection /> }
					{ activeNavItem === 'item-3' && (
						<PageFormTemplateNewOrder />
					) }
					{ activeNavItem === 'item-4' && (
						<PageFormTemplateNewOrderCustomer />
					) }
					{ activeNavItem === 'item-5' && (
						<PageFormTemplateUpdateOrderStatusCustomer />
					) }
					{ activeNavItem === 'item-6' && (
						<PageFormTemplateAbandonedCartCustomer />
					) }
					{ activeNavItem === 'item-7' && (
						<PageFormTemplateUnpaidOrderCustomer />
					) }
					{ activeNavItem === 'item-8' && (
						<PageFormTemplateOrderDetailsCustomer />
					) }
					{ activeNavItem === 'item-9' && (
						<PageFormReceivedMessages />
					) }
					{ activeNavItem === 'item-10' && (
						<PageFormAutoReplyReceivedMessages />
					) }
					{ activeNavItem === 'item-11' && (
						<PageStockNotifications />
					) }
					{ activeNavItem === 'item-12' && (
						<PageFormTemplateOutOfStockCustomer />
					) }
					{ activeNavItem === 'item-13' && (
						<PageFormTemplateBackInStockCustomer />
					) }
					{ activeNavItem === 'item-17' && (
						<PageFormTemplateReviewNotificationCustomer />
					) }
				{ activeNavItem === 'item-14' && <Coupon /> }
					{ activeNavItem === 'item-15' && <PageHelp /> }
					{ activeNavItem === 'item-16' && <About /> }
				</div>
			</div>
		</div>
	);
};
export default AdminPage;
