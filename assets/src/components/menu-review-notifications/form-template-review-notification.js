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
} from '@wordpress/components';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import apiFetch from '@wordpress/api-fetch';
import AddVariableModal from '../menu-message-templates/add-variable-modal';

const PageFormTemplateReviewNotificationCustomer = () => {
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
			fields.whatsapp_template_review_notification_customer_body + variable,
			'whatsapp_template_review_notification_customer_body'
		);
		closeVariableModal();
	};

	const { getFields, isLoading, isLoadingStatus } = useSelect(
		( select ) => ( {
			getFields: select( 'shop-arraycodes-order-notifications-woocommerce' ).getState(),
			isLoading: select(
				'shop-arraycodes-order-notifications-woocommerce'
			).hasFinishedResolution( 'getSettings' ),
			isLoadingStatus: select(
				'shop-arraycodes-order-notifications-woocommerce'
			).hasFinishedResolution( 'getReviewNotification' ),
		} ),
		[]
	);

	const [ fields, setFields ] = useState( {
		whatsapp_template_review_notification_customer_id: '',
		whatsapp_template_review_notification_customer_status: '',
		whatsapp_template_review_notification_customer_name: '',
		whatsapp_template_review_notification_customer_language: '',
		whatsapp_template_review_notification_customer_header: '',
		whatsapp_template_review_notification_customer_footer: '',
		whatsapp_template_review_notification_customer_body: '',
		whatsapp_template_review_notification_customer_order_status: '',
		whatsapp_template_review_notification_customer_days_trigger: '',
		whatsapp_template_review_notification_customer_button_text: '',
		whatsapp_template_review_notification_customer_button_url: '',
	} );

	const handleChange = useCallback( ( newText, fieldKey ) => {
		setFields( ( prevFields ) => ( {
			...prevFields,
			[ fieldKey ]: newText,
		} ) );
	}, [] );

	const currentState = useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getState()
	);

	useSelect( ( select ) =>
		select( 'shop-arraycodes-order-notifications-woocommerce' ).getReviewNotification()
	);

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-17' );
		window.location.reload();
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

	useEffect( () => {
		if ( getFields ) {
			const fetchedFields = getFields;
			setFields( ( prevFields ) => ( {
				...prevFields,
				...fetchedFields,
			} ) );
			if ( fetchedFields.whatsapp_template_review_notification_customer_id ) {
				setIsHaveId( true );
			}
		}
	}, [ getFields ] );

	const handleSave = async () => {
		try {
			setIsSaving( true );
			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_template_review_notification_customer_name:
					fields.whatsapp_template_review_notification_customer_name,
					whatsapp_template_review_notification_customer_language:
					fields.whatsapp_template_review_notification_customer_language,
					whatsapp_template_review_notification_customer_header:
					fields.whatsapp_template_review_notification_customer_header,
					whatsapp_template_review_notification_customer_footer:
					fields.whatsapp_template_review_notification_customer_footer,
					whatsapp_template_review_notification_customer_body:
					fields.whatsapp_template_review_notification_customer_body,
					whatsapp_template_review_notification_customer_order_status:
					fields.whatsapp_template_review_notification_customer_order_status,
					whatsapp_template_review_notification_customer_days_trigger:
					fields.whatsapp_template_review_notification_customer_days_trigger,
					whatsapp_template_review_notification_customer_button_text:
					fields.whatsapp_template_review_notification_customer_button_text,
					whatsapp_template_review_notification_customer_button_url:
					fields.whatsapp_template_review_notification_customer_button_url,
				},
				currentState
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

	const handleSentNewReviewNotificationCustomer = async () => {
		const dataToSend = {
			fields: getFields,
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
				'/wp-json/notifications-with-whatsapp/v1/new-review-notification-customer',
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

	const handleSentUpdateReviewNotificationCustomer = async () => {
		const dataToSend = {
			fields: getFields,
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
				'/wp-json/notifications-with-whatsapp/v1/update-review-notification-customer',
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

	const handleSentDeleteReviewNotificationCustomer = async () => {
		const dataToSend = {
			fields: getFields,
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
				'/wp-json/notifications-with-whatsapp/v1/delete-review-notification-customer',
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

	const isCouponMessageValid = getFields?.whatsapp_coupon_messages?.some(
		( message ) =>
			message.whatsapp_coupon_messages_name_template ===
			'review_notification_customer'
	);

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__(
					'Message Template Review Notification Customer',
					'arraycodes-order-notifications-woocommerce'
				)}
			/>
			<div className="form-legend">
				<Tip>
					{__(
						'Create the template message for review notification messages to the customer.',
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
export default PageFormTemplateReviewNotificationCustomer;
