/**
 * External dependencies
 */

import { Button, Panel, PanelBody, PanelRow } from '@wordpress/components';
import { plugins, commentContent, settings, comment, box, starFilled } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import NavSection from './nav-section';

const MyPanel = ( { onNavItemChange, activeNavItem } ) => {
	const handleItemClick = ( item ) => {
		onNavItemChange( item );
	};

	const [ newMessage, setNewMessage ] = useState( null );

	const [ showOrderDetails, setShowOrderDetails ] = useState( false );

	const fetchNewMessages = async () => {
		try {
			const messages = await apiFetch( {
				method: 'GET',
				path: '/notifications-with-whatsapp/v1/get-new-messages',
			} );
			if ( messages.result ) {
				setNewMessage( messages.result );
			}
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );
		}
	};

	const fetchFeatureFlag = async () => {
		try {
			const isEnabled = await apiFetch( {
				path: '/notifications-with-whatsapp/v1/is-order-details-enabled',
			} );
			setShowOrderDetails( isEnabled === true );
		} catch ( error ) {
			console.error( error );
		}
	};

	useEffect( () => {
		fetchNewMessages();
		fetchFeatureFlag();
	}, [] );

	return (
		<>
			<Panel>
				<PanelBody
					icon={ plugins }
					title={ __(
						'WhatsApp Connection',
						'arraycodes-order-notifications-woocommerce'
					) }
					initialOpen={ true }
				>
					<PanelRow>
						<NavSection
							activeItem={ activeNavItem }
							onItemClick={ handleItemClick }
							items={ [
								{
									item: 'item-1',
									title: __(
										'Settings',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-2',
									title: __(
										'Webhook',
										'arraycodes-order-notifications-woocommerce'
									),
								},
							] }
						/>
					</PanelRow>
				</PanelBody>
				<PanelBody
					title={ __(
						'Message Templates',
						'arraycodes-order-notifications-woocommerce'
					) }
					icon={ commentContent }
					initialOpen={ true }
				>
					<PanelRow>
						<NavSection
							activeItem={ activeNavItem }
							onItemClick={ handleItemClick }
							items={ [
								{
									item: 'item-3',
									title: __(
										'New Order Seller',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-4',
									title: __(
										'New Order Customer',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-5',
									title: __(
										'Update Order Status Customer',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-6',
									title: __(
										'Abandoned Cart Customer',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-7',
									title: __(
										'Unpaid Order Customer',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								...( showOrderDetails
								? [ {
									item: 'item-8',
									title: __(
										'Order Details Customer',
										'arraycodes-order-notifications-woocommerce'
									),
								} ]
								: [] )
							] }
						/>
					</PanelRow>
				</PanelBody>

				<PanelBody
					title={ __(
						'Received Messages',
						'arraycodes-order-notifications-woocommerce'
					) }
					icon={ comment }
					initialOpen={ true }
				>
					<PanelRow>
						<div className={ 'div-received-messages'}>
							<NavSection
								activeItem={ activeNavItem }
								onItemClick={ handleItemClick }
								items={ [
									{
										item: 'item-9',
										title: __(
											'View Messages',
											'arraycodes-order-notifications-woocommerce'
										),
									},
									{
										item: 'item-10',
										title: __(
											'Auto-reply Message',
											'arraycodes-order-notifications-woocommerce'
										),
									}
								] }
							/>
							<div
								className="div-new-message"
								dangerouslySetInnerHTML={ {
									__html: newMessage
										? `<span class="new-message-count">${ newMessage }</span>`
										: '',
								} }
							/>
						</div>
					</PanelRow>
				</PanelBody>

				<PanelBody
					title={ __(
						'In-Stock Notifications',
						'arraycodes-order-notifications-woocommerce'
					) }
					icon={ box }
					initialOpen={ true }
				>
					<PanelRow>
						<div className={ 'div-received-messages'}>
							<NavSection
								activeItem={ activeNavItem }
								onItemClick={ handleItemClick }
								items={ [
									{
										item: 'item-11',
										title: __(
											'View Requests',
											'arraycodes-order-notifications-woocommerce'
										),
									},
								] }
							/>
							<NavSection
								activeItem={ activeNavItem }
								onItemClick={ handleItemClick }
								items={ [
									{
										item: 'item-12',
										title: __(
											'Out of stock Message',
											'arraycodes-order-notifications-woocommerce'
										),
									},
								] }
							/>
							<NavSection
								activeItem={ activeNavItem }
								onItemClick={ handleItemClick }
								items={ [
									{
										item: 'item-13',
										title: __(
											'Back in Stock Message',
											'arraycodes-order-notifications-woocommerce'
										),
									},
								] }
							/>
						</div>
					</PanelRow>
				</PanelBody>

				<PanelBody
					title={ __(
						'Reviews Notifications',
						'arraycodes-order-notifications-woocommerce'
					) }
					icon={ starFilled }
					initialOpen={ true }
				>
					<PanelRow>
						<div className={ 'div-received-messages'}>
							<NavSection
								activeItem={ activeNavItem }
								onItemClick={ handleItemClick }
								items={ [
									{
										item: 'item-17',
										title: __(
											'Review Message',
											'arraycodes-order-notifications-woocommerce'
										),
									},
								] }
							/>
						</div>
					</PanelRow>
				</PanelBody>

			<PanelBody title="Extra" icon={ settings } initialOpen={ true }>
					<PanelRow>
						<NavSection
							activeItem={ activeNavItem }
							onItemClick={ handleItemClick }
							items={ [
								{
									item: 'item-14',
									title: __(
										'Coupon',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-15',
									title: __(
										'Help',
										'arraycodes-order-notifications-woocommerce'
									),
								},
								{
									item: 'item-16',
									title: __(
										'About',
										'arraycodes-order-notifications-woocommerce'
									),
								}
							] }
						/>
					</PanelRow>
				</PanelBody>
			</Panel>
		</>
	);
};
export default MyPanel;
