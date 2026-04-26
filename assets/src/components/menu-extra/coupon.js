/**
 * External dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Button,
	Spinner,
	Tip,
	TextControl,
	SelectControl,
	ToggleControl, ExternalLink,
} from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const Coupon = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const dispatch = useDispatch();

	const [ isSaving, setIsSaving ] = useState( false );
	const [ isSenting, setIsSenting ] = useState( false );
	const [ currentlyDeletingId, setCurrentlyDeletingId ] = useState( null );
	const [ isCreateMessage, setCreateMessage ] = useState( false );
	const openCreateMessage = () => setCreateMessage( true );
	const closeCreateMessage = () => setCreateMessage( false );

	const { getFields, isLoading } = useSelect(
		( select ) => ( {
			getFields: select( 'shop-arraycodes-order-notifications-woocommerce' ).getState(),
			isLoading: select(
				'shop-arraycodes-order-notifications-woocommerce'
			).hasFinishedResolution( 'getSettings' ),
		} ),
		[]
	);
	const [ fields, setFields ] = useState( {
		whatsapp_coupon_messages: [],
	} );
	const [ editedCouponMessagesFields, setEditedOrderStatusCustomerFields ] =
		useState( {} );

	const handleChange = useCallback( ( newText, fieldKey ) => {
		setEditedOrderStatusCustomerFields( ( prevFields ) => ( {
			...prevFields,
			[ fieldKey ]: newText,
		} ) );
	}, [] );

	const currentState = useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getState()
	);

	useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getOrderStatusCustomer()
	);

	useEffect( () => {
		if ( getFields ) {
			const fetchedFields = getFields;
			setFields( ( prevFields ) => ( {
				...prevFields,
				...fetchedFields,
			} ) );
		}
	}, [ getFields ] );

	const openCreateForm = () => {
		const couponMessages = fields.whatsapp_coupon_messages;
		let newId = 1;

		if ( couponMessages.length > 0 ) {
			const maxId = Math.max(
				...couponMessages.map( ( couponMessage ) =>
					parseInt( couponMessage.id, 10 )
				)
			);
			newId = maxId + 1;
		}

		const couponMessageToEdit = {
			id: newId,
			whatsapp_coupon_messages_id_coupon: '',
			whatsapp_coupon_messages_name_template: '',
			whatsapp_coupon_messages_coupon_code: '',
			whatsapp_coupon_messages_discount_type: '',
			whatsapp_coupon_messages_coupon_amount: '',
			whatsapp_coupon_messages_coupon_expiry_date: '',
			whatsapp_coupon_messages_coupon_individual_only: '',
			whatsapp_coupon_messages_add_button: '',
		};
		setEditedOrderStatusCustomerFields( couponMessageToEdit );
		openCreateMessage();
	};

	const handleEdit = ( couponMessageId ) => {
		openCreateMessage();
		const couponMessageToEdit = fields.whatsapp_coupon_messages.find(
			( couponMessage ) => couponMessage.id === couponMessageId
		);
		setEditedOrderStatusCustomerFields( couponMessageToEdit );
	};

	const handleSave = async () => {
		try {
			setIsSaving( true );

			if (
				editedCouponMessagesFields?.whatsapp_coupon_messages_id_coupon
			) {
				await apiFetch( {
					path: '/arraycodes-order-notifications-woocommerce/v1/update-coupon',
					method: 'POST',
					data: editedCouponMessagesFields,
				} );
			} else {
				const response = await apiFetch( {
					path: '/arraycodes-order-notifications-woocommerce/v1/create-coupon',
					method: 'POST',
					data: editedCouponMessagesFields,
				} );
				editedCouponMessagesFields.whatsapp_coupon_messages_id_coupon =
					response.id;
			}

			const updatedOrderStatusCustomers =
				fields.whatsapp_coupon_messages.map( ( couponMessage ) =>
					couponMessage.id === editedCouponMessagesFields.id
						? {
								...couponMessage,
								...editedCouponMessagesFields,
						  }
						: couponMessage
				);

			if (
				! fields.whatsapp_coupon_messages.find(
					( couponMessage ) =>
						couponMessage.id === editedCouponMessagesFields.id
				)
			) {
				updatedOrderStatusCustomers.push( editedCouponMessagesFields );
			}

			setFields( {
				...fields,
				whatsapp_coupon_messages: updatedOrderStatusCustomers,
			} );

			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_coupon_messages: updatedOrderStatusCustomers,
				},
				getFields
			);

			setIsSaving( false );
			closeCreateMessage();

			createSuccessNotice(
				__( 'Coupon saved!', 'arraycodes-order-notifications-woocommerce' ),
				{
					type: 'snackbar',
				}
			);
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.log( 'Error saving settings:', error );

			const errorData = error.data?.params || {};
			const formattedMessages = Object.keys( errorData ).map( ( key ) =>
				key
					.replace( /^whatsapp_coupon_messages_/, '' )
					.replace( /_/g, ' ' )
					.replace( /\b\w/g, ( char ) => char.toUpperCase() )
			);

			createErrorNotice(
				'An error occurred while saving settings: ' +
					( formattedMessages.length > 0
						? formattedMessages.join( ', ' )
						: error.message ),
				{
					type: 'snackbar',
				}
			);
		} finally {
			setIsSaving( false );
		}
	};

	const handleDelete = async ( couponMessageId, idCouponWp ) => {
		try {
			setIsSenting( true );

			setCurrentlyDeletingId( couponMessageId );

			await apiFetch( {
				path: '/arraycodes-order-notifications-woocommerce/v1/delete-coupon',
				method: 'POST',
				data: { id: idCouponWp },
			} );

			const updatedOrderStatusCustomers =
				fields.whatsapp_coupon_messages.filter(
					( couponMessage ) => couponMessage.id !== couponMessageId
				);

			setFields( {
				...fields,
				whatsapp_coupon_messages: updatedOrderStatusCustomers,
			} );

			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_coupon_messages: updatedOrderStatusCustomers,
				},
				currentState
			);

			setIsSenting( false );
			setCurrentlyDeletingId( null );

			createSuccessNotice(
				__( 'Coupon deleted!', 'arraycodes-order-notifications-woocommerce' ),
				{
					type: 'snackbar',
				}
			);
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error deleting order_status_customer:', error );
			setCurrentlyDeletingId( null );
			createErrorNotice(
				'An error occurred while deleting the order_status_customer.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__('Coupon', 'arraycodes-order-notifications-woocommerce')}
			/>
			<div className="form-legend">
				<Tip>
					{__(
						'Create coupons with custom rules to send in customer messages.',
						'arraycodes-order-notifications-woocommerce'
					)}
				</Tip>
				<br />
				<br />
			</div>
			{!isLoading ? (
				<Spinner
					style={{
						height: 'calc(4px * 20)',
						width: 'calc(4px * 20)',
					}}
				/>
			) : (
				<>
					<div className="premium-function">
						<span>
							<ExternalLink
								href="https://woocommerce.com/products/notifications-with-whatsapp/"
								className="link-premium-function"
							>
								Unlock with Premium
							</ExternalLink>
						</span>
						<table className="wp-list-table widefat fixed striped table-view-list">
							<thead>
								<tr>
									<td>
										{__(
											'ID Coupon',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td>
										{__(
											'Coupon code',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td>
										{__(
											'Message template',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td style={{ width: 450 }}>
										{__(
											'Actions',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
								</tr>
							</thead>
						</table>
						<div className="form-fields-button">
							<Button onClick={openCreateForm} disabled variant="primary">
								{__(
									'Create',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Button>
						</div>
					</div>
				</>
			)}
		</div>
	);
};
export default Coupon;
