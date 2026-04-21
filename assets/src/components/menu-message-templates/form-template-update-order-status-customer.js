/**
 * External dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Button,
	Spinner,
	Tip,
	ExternalLink,
	TextControl,
	TextareaControl,
	Modal,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import ButtonsList from './buttons-list';
import AddVariableModal from './add-variable-modal';

const PageFormTemplateUpdateOrderStatusCustomer = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const dispatch = useDispatch();

	const [ isSaving, setIsSaving ] = useState( false );
	const [ isSenting, setIsSenting ] = useState( false );
	const [ currentlyDeletingId, setCurrentlyDeletingId ] = useState( null );
	const [ currentlyCreatingApiId, setCurrentlyCreatingApiId ] =
		useState( null );
	const [ currentlyUpdatingApiId, setCurrentlyUpdatingApiId ] =
		useState( null );
	const [ currentlyExcludingApiId, setCurrentlyExcludingApiId ] =
		useState( null );
	const [ isCreateMessage, setCreateMessage ] = useState( false );
	const [ isOpen, setOpen ] = useState( false );
	const openCreateMessage = () => setCreateMessage( true );
	const closeCreateMessage = () => setCreateMessage( false );

	const [ isModalVariableOpen, setModalVariableOpen ] = useState( false );
	const openVariableModal = () => setModalVariableOpen( true );
	const closeVariableModal = () => setModalVariableOpen( false );
	const handleSelectVariableFromParent = ( variable ) => {
		handleChange(
			editedOrderStatusCustomerFields.order_status_customer_body +
				variable,
			'order_status_customer_body'
		);
		closeVariableModal();
	};

	const [ statusOrder, setStatusOrder ] = useState( null );

	const [ showStatusOrder, setShowStatusOrder ] = useState( false );

	const { getFields, isLoading, isLoadingStatus } = useSelect(
		( select ) => ( {
			getFields: select( 'shop-arraycodes-order-notifications-woocommerce' ).getState(),
			isLoading: select(
				'shop-arraycodes-order-notifications-woocommerce'
			).hasFinishedResolution( 'getSettings' ),
			isLoadingStatus: select(
				'shop-arraycodes-order-notifications-woocommerce'
			).hasFinishedResolution( 'getOrderStatusCustomer' ),
		} ),
		[]
	);
	const [ fields, setFields ] = useState( {
		whatsapp_template_update_order_status_customer: [],
	} );
	const [
		editedOrderStatusCustomerFields,
		setEditedOrderStatusCustomerFields,
	] = useState( {} );

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

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-5' );
		window.location.reload();
	};

	const fetchFeatureFlag = async () => {
		try {
			const isEnabled = await apiFetch( {
				path: '/notifications-with-whatsapp/v1/is-order-details-enabled',
			} );
			setShowStatusOrder( isEnabled === true );
		} catch ( error ) {
			console.error( error );
		}
	};

	useEffect( () => {
		fetchFeatureFlag();
		if ( getFields ) {
			const fetchedFields = getFields;
			setFields( ( prevFields ) => ( {
				...prevFields,
				...fetchedFields,
			} ) );
		}
	}, [ getFields ] );

	const openCreateForm = () => {
		const orderStatusCustomers =
			fields.whatsapp_template_update_order_status_customer;
		let newId = 1;

		if ( orderStatusCustomers.length > 0 ) {
			const maxId = Math.max(
				...orderStatusCustomers.map( ( orderStatusCustomer ) =>
					parseInt( orderStatusCustomer.id, 10 )
				)
			);
			newId = maxId + 1;
		}

		const orderStatusCustomerToEdit = {
			id: newId,
			order_status_customer_id: '',
			order_status_customer_status: '',
			order_status_customer_status_meta: '',
			order_status_customer_name: '',
			order_status_customer_language: '',
			order_status_customer_header: '',
			order_status_customer_footer: '',
			order_status_customer_body: '',
			order_status_customer_order_status: false,
			order_status_customer_buttons: [],
		};
		setEditedOrderStatusCustomerFields( orderStatusCustomerToEdit );
		openCreateMessage();
	};

	const handleEdit = ( orderStatusCustomerId ) => {
		openCreateMessage();
		const orderStatusCustomerToEdit =
			fields.whatsapp_template_update_order_status_customer.find(
				( orderStatusCustomer ) =>
					orderStatusCustomer.id === orderStatusCustomerId
			);
		setEditedOrderStatusCustomerFields( orderStatusCustomerToEdit );
	};

	const handleSave = async () => {
		try {
			setIsSaving( true );

			const updatedOrderStatusCustomers =
				fields.whatsapp_template_update_order_status_customer.map(
					( orderStatusCustomer ) =>
						orderStatusCustomer.id ===
						editedOrderStatusCustomerFields.id
							? {
									...orderStatusCustomer,
									...editedOrderStatusCustomerFields,
							  }
							: orderStatusCustomer
				);

			if (
				! fields.whatsapp_template_update_order_status_customer.find(
					( orderStatusCustomer ) =>
						orderStatusCustomer.id ===
						editedOrderStatusCustomerFields.id
				)
			) {
				updatedOrderStatusCustomers.push(
					editedOrderStatusCustomerFields
				);
			}

			setFields( {
				...fields,
				whatsapp_template_update_order_status_customer:
					updatedOrderStatusCustomers,
			} );

			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_template_update_order_status_customer:
						updatedOrderStatusCustomers,
				},
				currentState
			);

			setIsSaving( false );
			closeCreateMessage();

			createSuccessNotice(
				__(
					'Order Status Customer saved!',
					'arraycodes-order-notifications-woocommerce'
				),
				{
					type: 'snackbar',
				}
			);
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error saving settings:', error );
			createErrorNotice( 'An error occurred while saving settings.', {
				type: 'snackbar',
			} );
		}
	};

	const handleDelete = async ( orderStatusCustomerId ) => {
		try {
			setIsSenting( true );

			setCurrentlyDeletingId( orderStatusCustomerId );

			const updatedOrderStatusCustomers =
				fields.whatsapp_template_update_order_status_customer.filter(
					( orderStatusCustomer ) =>
						orderStatusCustomer.id !== orderStatusCustomerId
				);

			setFields( {
				...fields,
				whatsapp_template_update_order_status_customer:
					updatedOrderStatusCustomers,
			} );

			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_template_update_order_status_customer:
						updatedOrderStatusCustomers,
				},
				currentState
			);

			setIsSenting( false );
			setCurrentlyDeletingId( null );

			createSuccessNotice(
				__(
					'Order Status Customer deleted!',
					'arraycodes-order-notifications-woocommerce'
				),
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

	const handleSentApiNewOrderStatusCustomer = async (
		orderStatusCustomerId
	) => {
		const dataToSend = {
			fields: getFields,
		};
		const dataToSendFilter =
			dataToSend.fields.whatsapp_template_update_order_status_customer
				.map( ( orderStatusCustomer ) => {
					if ( orderStatusCustomer.id === orderStatusCustomerId ) {
						return orderStatusCustomer;
					}
					return null;
				} )
				.filter( Boolean );
		const dataToSendFinal = {
			fields: dataToSendFilter[ 0 ],
		};
		const arraycodesOnVars = window.arraycodesOnVars;
		const securityNonce = arraycodesOnVars.security;

		try {
			setIsSenting( true );
			setCurrentlyCreatingApiId( orderStatusCustomerId );

			const requestData = {
				...dataToSendFinal,
				security: securityNonce,
			};

			const fetchOptions = {
				method: 'POST',
				credentials: 'include',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
				body: JSON.stringify( requestData ),
			};

			const response = await fetch(
				'/wp-json/notifications-with-whatsapp/v1/new-order-status-customer',
				fetchOptions
			);

			if ( response.ok ) {
				const responseData = await response.json();
				// eslint-disable-next-line no-console
				console.log( 'Server response:', responseData );
				if ( responseData.message.code === 100 ) {
					createErrorNotice( responseData.message.error_user_msg, {
						explicitDismiss: true,
						type: 'snackbar',
						icon: '⛔',
					} );
				} else {
					handleSetStorageAndReloadPage();
				}
				setIsSenting( false );
				setCurrentlyCreatingApiId( null );
			} else {
				// eslint-disable-next-line no-console
				console.error(
					'Server responded with an error:',
					response.statusText
				);
				createErrorNotice(
					'An error occurred while sending data to WordPress.',
					{
						type: 'snackbar',
					}
				);
			}
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error sending data to WordPress:', error );
			setCurrentlyCreatingApiId( null );
			createErrorNotice(
				'An error occurred while sending data to WordPress.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	const handleSentApiUpdateOrderStatusCustomer = async (
		orderStatusCustomerId
	) => {
		const dataToSend = {
			fields: getFields,
		};
		const dataToSendFilter =
			dataToSend.fields.whatsapp_template_update_order_status_customer
				.map( ( orderStatusCustomer ) => {
					if ( orderStatusCustomer.id === orderStatusCustomerId ) {
						return orderStatusCustomer;
					}
					return null;
				} )
				.filter( Boolean );
		const dataToSendFinal = {
			fields: dataToSendFilter[ 0 ],
		};
		const arraycodesOnVars = window.arraycodesOnVars;
		const securityNonce = arraycodesOnVars.security;

		try {
			setIsSenting( true );
			setCurrentlyUpdatingApiId( orderStatusCustomerId );

			const requestData = {
				...dataToSendFinal,
				security: securityNonce,
			};

			const fetchOptions = {
				method: 'POST',
				credentials: 'include',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
				body: JSON.stringify( requestData ),
			};

			const response = await fetch(
				'/wp-json/notifications-with-whatsapp/v1/update-order-status-customer',
				fetchOptions
			);

			if ( response.ok ) {
				const responseData = await response.json();
				// eslint-disable-next-line no-console
				console.log( 'Server response:', responseData );
				if ( responseData.message.code === 100 ) {
					createErrorNotice( responseData.message.error_user_msg, {
						explicitDismiss: true,
						type: 'snackbar',
						icon: '⛔',
					} );
				} else {
					handleSetStorageAndReloadPage();
				}
				setIsSenting( false );
				setCurrentlyUpdatingApiId( null );
			} else {
				// eslint-disable-next-line no-console
				console.error(
					'Server responded with an error:',
					response.statusText
				);
				createErrorNotice(
					'An error occurred while sending data to WordPress.',
					{
						type: 'snackbar',
					}
				);
			}
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error sending data to WordPress:', error );
			setCurrentlyUpdatingApiId( null );
			createErrorNotice(
				'An error occurred while sending data to WordPress.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	const handleSentApiDeleteOrderStatusCustomer = async (
		orderStatusCustomerId
	) => {
		const dataToSend = {
			fields: getFields,
		};
		const dataToSendFilter =
			dataToSend.fields.whatsapp_template_update_order_status_customer
				.map( ( orderStatusCustomer ) => {
					if ( orderStatusCustomer.id === orderStatusCustomerId ) {
						return orderStatusCustomer;
					}
					return null;
				} )
				.filter( Boolean );
		const dataToSendFinal = {
			fields: dataToSendFilter[ 0 ],
		};
		const arraycodesOnVars = window.arraycodesOnVars;
		const securityNonce = arraycodesOnVars.security;

		try {
			setIsSenting( true );
			setCurrentlyExcludingApiId( orderStatusCustomerId );

			const requestData = {
				...dataToSendFinal,
				security: securityNonce,
			};

			const fetchOptions = {
				method: 'POST',
				credentials: 'include',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
				body: JSON.stringify( requestData ),
			};

			const response = await fetch(
				'/wp-json/notifications-with-whatsapp/v1/delete-order-status-customer',
				fetchOptions
			);

			if ( response.ok ) {
				const responseData = await response.json();
				// eslint-disable-next-line no-console
				console.log( 'Server response:', responseData );
				if ( responseData.message.code === 100 ) {
					createErrorNotice( responseData.message.error_user_msg, {
						explicitDismiss: true,
						type: 'snackbar',
						icon: '⛔',
					} );
				} else {
					handleSetStorageAndReloadPage();
				}
				setIsSenting( false );
				setCurrentlyExcludingApiId( null );
			} else {
				// eslint-disable-next-line no-console
				console.error(
					'Server responded with an error:',
					response.statusText
				);
				createErrorNotice(
					'An error occurred while sending data to WordPress.',
					{
						type: 'snackbar',
					}
				);
			}
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( 'Error sending data to WordPress:', error );
			setCurrentlyExcludingApiId( null );
			createErrorNotice(
				'An error occurred while sending data to WordPress.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	const fetchStatusOrder = async () => {
		try {
			const messages = await apiFetch( {
				method: 'GET',
				path: '/notifications-with-whatsapp/v1/get-status-order',
			} );
			if ( messages.result ) {
				setStatusOrder( messages.result );
			}
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );
		}
	};

	useEffect( () => {
		fetchStatusOrder();
	}, [] );

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__(
					'Message Template Update Order Status Customer',
					'arraycodes-order-notifications-woocommerce'
				)}
			/>
			<div className="form-legend">
				<Tip>
					{__(
						'Create the template message for sending update order status messages to the customer.',
						'arraycodes-order-notifications-woocommerce'
					)}{' '}
					<ExternalLink href="https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates">
						{__('Read more', 'arraycodes-order-notifications-woocommerce')}
					</ExternalLink>
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
											'Template id',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td>
										{__(
											'Template name',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td>
										{__(
											'Status',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td>
										{__(
											'Status Meta',
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
export default PageFormTemplateUpdateOrderStatusCustomer;
