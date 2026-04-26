/**
 * External dependencies
 */
import {
	Button,
	Tip,
	ExternalLink,
	TextControl,
	SelectControl,
	TextareaControl,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import apiFetch from '@wordpress/api-fetch';
import AddVariableModal from './add-variable-modal';

const PageFormTemplateUnpaidOrderCustomer = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const dispatch = useDispatch();

	const [ isSaving, setIsSaving ] = useState( false );
	const [ isSenting, setIsSenting ] = useState( false );
	const [ isHaveId, setIsHaveId ] = useState( false );
	const [ isCreateMessage, setCreateMessage ] = useState( false );
	const [ isModalVariableOpen, setModalVariableOpen ] = useState( false );
	const [ statusOrder, setStatusOrder ] = useState( null );

	const openCreateMessage = () => setCreateMessage( true );
	const closeCreateMessage = () => setCreateMessage( false );
	const openVariableModal = () => setModalVariableOpen( true );
	const closeVariableModal = () => setModalVariableOpen( false );
	const handleSelectVariableFromParent = ( variable ) => {
		handleChange(
			fields.whatsapp_template_unpaid_order_customer_body + variable,
			'whatsapp_template_unpaid_order_customer_body'
		);
		closeVariableModal();
	};

	const fieldsFromStore = useSelect(
		( select ) =>
			select( 'shop-arraycodes-order-notifications-woocommerce' ).getSettings(),
		[]
	);

	const isLoading = useSelect(
		( select ) =>
			select( 'shop-arraycodes-order-notifications-woocommerce' ).hasFinishedResolution(
				'getSettings'
			),
		[]
	);

	const isLoadingStatus = useSelect(
		( select ) =>
			select( 'shop-arraycodes-order-notifications-woocommerce' ).hasFinishedResolution(
				'getUnpaidOrder'
			),
		[]
	);

	const [ fields, setFields ] = useState( {
		whatsapp_template_unpaid_order_customer_id: '',
		whatsapp_template_unpaid_order_customer_status: '',
		whatsapp_template_unpaid_order_customer_name: '',
		whatsapp_template_unpaid_order_customer_language: '',
		whatsapp_template_unpaid_order_customer_header: '',
		whatsapp_template_unpaid_order_customer_footer: '',
		whatsapp_template_unpaid_order_customer_body: '',
		whatsapp_template_unpaid_order_customer_order_status: '',
		whatsapp_template_unpaid_order_customer_minutes_trigger: '',
		whatsapp_template_unpaid_order_customer_button: false,
	} );

	const handleChange = useCallback( ( newText, fieldKey ) => {
		setFields( ( prevFields ) => ( {
			...prevFields,
			[ fieldKey ]: newText,
		} ) );
	}, [] );

	useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getUnpaidOrder()
	);

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-7' );
		window.location.reload();
	};

	useEffect( () => {
		if ( fieldsFromStore ) {
			setFields( ( prevFields ) => ( {
				...prevFields,
				...fieldsFromStore,
			} ) );

			if ( fieldsFromStore.whatsapp_template_unpaid_order_customer_id ) {
				setIsHaveId( true );
			} else {
				setIsHaveId( false );
			}
		}
	}, [ fieldsFromStore ] );

	const fetchStatusOrder = async () => {
		try {
			const messages = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/get-status-order',
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

	const handleSave = async () => {
		try {
			setIsSaving( true );
			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_template_unpaid_order_customer_name:
						fields.whatsapp_template_unpaid_order_customer_name,
					whatsapp_template_unpaid_order_customer_language:
						fields.whatsapp_template_unpaid_order_customer_language,
					whatsapp_template_unpaid_order_customer_header:
						fields.whatsapp_template_unpaid_order_customer_header,
					whatsapp_template_unpaid_order_customer_footer:
						fields.whatsapp_template_unpaid_order_customer_footer,
					whatsapp_template_unpaid_order_customer_body:
						fields.whatsapp_template_unpaid_order_customer_body,
					whatsapp_template_unpaid_order_customer_order_status:
						fields.whatsapp_template_unpaid_order_customer_order_status,
					whatsapp_template_unpaid_order_customer_minutes_trigger:
						fields.whatsapp_template_unpaid_order_customer_minutes_trigger,
					whatsapp_template_unpaid_order_customer_button:
						fields.whatsapp_template_unpaid_order_customer_button,
				},
				fieldsFromStore
			);

			setIsSaving( false );
			closeCreateMessage();

			createSuccessNotice(
				__( 'Message saved!', 'arraycodes-order-notifications-woocommerce' ),
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

	const handleSentNewUnpaidOrderCustomer = async () => {
		const dataToSend = {
			fields,
		};
		const arraycodesOnVars = window.arraycodesOnVars;
		const securityNonce = arraycodesOnVars.security;

		try {
			setIsSenting( true );

			const requestData = {
				...dataToSend,
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
				'/wp-json/arraycodes-order-notifications-woocommerce/v1/new-unpaid-order-customer',
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
			createErrorNotice(
				'An error occurred while sending data to WordPress.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	const handleSentUpdateUnpaidOrderCustomer = async () => {
		const dataToSend = {
			fields,
		};
		const arraycodesOnVars = window.arraycodesOnVars;
		const securityNonce = arraycodesOnVars.security;

		try {
			setIsSenting( true );

			const requestData = {
				...dataToSend,
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
				'/wp-json/arraycodes-order-notifications-woocommerce/v1/update-unpaid-order-customer',
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
			createErrorNotice(
				'An error occurred while sending data to WordPress.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	const handleSentDeleteUnpaidOrderCustomer = async () => {
		const dataToSend = {
			fields,
		};
		const arraycodesOnVars = window.arraycodesOnVars;
		const securityNonce = arraycodesOnVars.security;

		try {
			setIsSenting( true );

			const requestData = {
				...dataToSend,
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
				'/wp-json/arraycodes-order-notifications-woocommerce/v1/delete-unpaid-order-customer',
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
			createErrorNotice(
				'An error occurred while sending data to WordPress.',
				{
					type: 'snackbar',
				}
			);
		}
	};

	const isCouponMessageValid =
		fieldsFromStore?.whatsapp_coupon_messages?.some(
			( message ) =>
				message.whatsapp_coupon_messages_name_template ===
				'unpaid_order_customer'
		);

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__(
					'Message Unpaid Order Customer',
					'arraycodes-order-notifications-woocommerce'
				)}
			/>
			<div className="form-legend">
				<Tip>
					{__(
						'Create message template to send unpaid order messages to customer.',
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
							<Button
								onClick={openCreateMessage}
								variant="primary"
								disabled
							>
								{isHaveId
									? __(
											'Edit Message',
											'arraycodes-order-notifications-woocommerce'
										)
									: __(
											'Create Message',
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
export default PageFormTemplateUnpaidOrderCustomer;
