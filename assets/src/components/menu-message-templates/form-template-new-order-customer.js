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
import AddVariableModal from './add-variable-modal';

const PageFormTemplateNewOrderCustomer = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const dispatch = useDispatch();

	const [ isSaving, setIsSaving ] = useState( false );
	const [ isSenting, setIsSenting ] = useState( false );
	const [ isHaveId, setIsHaveId ] = useState( false );
	const [ isCreateMessage, setCreateMessage ] = useState( false );
	const [ isModalVariableOpen, setModalVariableOpen ] = useState( false );
	const openCreateMessage = () => setCreateMessage( true );
	const closeCreateMessage = () => setCreateMessage( false );
	const openVariableModal = () => setModalVariableOpen( true );
	const closeVariableModal = () => setModalVariableOpen( false );
	const handleSelectVariableFromParent = ( variable ) => {
		handleChange(
			fields.whatsapp_template_new_order_customer_body + variable,
			'whatsapp_template_new_order_customer_body'
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
				'getOrderCustomer'
			),
		[]
	);

	const [ fields, setFields ] = useState( {
		whatsapp_template_new_order_customer_id: '',
		whatsapp_template_new_order_customer_status: '',
		whatsapp_template_new_order_customer_name: '',
		whatsapp_template_new_order_customer_language: '',
		whatsapp_template_new_order_customer_header: '',
		whatsapp_template_new_order_customer_header_type: '',
		whatsapp_template_new_order_customer_footer: '',
		whatsapp_template_new_order_customer_body: '',
	} );

	const handleChange = useCallback( ( newText, fieldKey ) => {
		setFields( ( prevFields ) => ( {
			...prevFields,
			[ fieldKey ]: newText,
		} ) );
	}, [] );

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-4' );
		window.location.reload();
	};

	useEffect( () => {
		if ( fieldsFromStore ) {
			setFields( ( prevFields ) => ( {
				...prevFields,
				...fieldsFromStore,
			} ) );

			if ( fieldsFromStore.whatsapp_template_new_order_customer_id ) {
				setIsHaveId( true );
			} else {
				setIsHaveId( false );
			}
		}
	}, [ fieldsFromStore ] );

	useSelect(
		( select ) =>
			select( 'shop-arraycodes-order-notifications-woocommerce' ).getOrderCustomer(),
		[]
	);

	const getHeaderHelp = ( headerType ) => {
		switch ( headerType ) {
			case 'text':
				return __(
					'Insert a short text as the header of your message template.',
					'arraycodes-order-notifications-woocommerce'
				);
			case 'image':
				return __(
					'Provide the URL of the image used as header.',
					'arraycodes-order-notifications-woocommerce'
				);
			default:
				return __(
					'Optional header for your message template.',
					'arraycodes-order-notifications-woocommerce'
				);
		}
	};

	const headerType = fields.whatsapp_template_new_order_customer_header_type;

	const handleSave = async () => {
		try {
			setIsSaving( true );
			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_template_new_order_customer_name:
						fields.whatsapp_template_new_order_customer_name,
					whatsapp_template_new_order_customer_language:
						fields.whatsapp_template_new_order_customer_language,
					whatsapp_template_new_order_customer_header:
						fields.whatsapp_template_new_order_customer_header,
					whatsapp_template_new_order_customer_header_type:
						fields.whatsapp_template_new_order_customer_header_type,
					whatsapp_template_new_order_customer_footer:
						fields.whatsapp_template_new_order_customer_footer,
					whatsapp_template_new_order_customer_body:
						fields.whatsapp_template_new_order_customer_body,
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

	const handleSentNewOrderCustomer = async () => {
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
				'/wp-json/arraycodes-order-notifications-woocommerce/v1/new-order-customer',
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

	const handleSentUpdateOrderCustomer = async () => {
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
				'/wp-json/arraycodes-order-notifications-woocommerce/v1/update-order-customer',
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

	const handleSentDeleteOrderCustomer = async () => {
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
				'/wp-json/arraycodes-order-notifications-woocommerce/v1/delete-order-customer',
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

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={ __(
					'Message Template New Order Customer',
					'arraycodes-order-notifications-woocommerce'
				) }
			/>
			<div className="form-legend">
				<Tip>
					{ __(
						'Create the template message for sending new order messages to the customer.',
						'arraycodes-order-notifications-woocommerce'
					) }{ ' ' }
					<ExternalLink href="https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-message-templates">
						{ __( 'Read more', 'arraycodes-order-notifications-woocommerce' ) }
					</ExternalLink>
				</Tip>
				<br />
				<br />
			</div>
			{ ! isLoading ? (
				<Spinner
					style={ {
						height: 'calc(4px * 20)',
						width: 'calc(4px * 20)',
					} }
				/>
			) : (
				<>
					{ ! isCreateMessage ? (
						<>
							<table className="wp-list-table widefat fixed striped table-view-list">
								<thead>
									<tr>
										<td>
											{ __(
												'Template id',
												'arraycodes-order-notifications-woocommerce'
											) }
										</td>
										<td>
											{ __(
												'Template name',
												'arraycodes-order-notifications-woocommerce'
											) }
										</td>
										<td>
											{ __(
												'Status Meta',
												'arraycodes-order-notifications-woocommerce'
											) }
										</td>
										<td style={ { width: 450 } }>
											{ __(
												'Actions',
												'arraycodes-order-notifications-woocommerce'
											) }
										</td>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											{
												fields.whatsapp_template_new_order_customer_id
											}
										</td>
										<td>
											{
												fields.whatsapp_template_new_order_customer_name
											}
										</td>
										<td>
											{ ! isLoadingStatus ? (
												<Spinner />
											) : (
												<>
													{
														fields.whatsapp_template_new_order_customer_status
													}
													{ fields.whatsapp_template_new_order_customer_category ===
														'MARKETING' && (
														<div
															style={ {
																marginTop:
																	'6px',
																padding:
																	'4px 8px',
																backgroundColor:
																	'#fcf0d3',
																border: '1px solid #f0b429',
																borderRadius:
																	'4px',
																fontSize:
																	'12px',
																color: '#7d4e00',
																display: 'flex',
																alignItems:
																	'center',
																gap: '4px',
															} }
														>
															<span>
																{ __(
																	'Meta reclassified this template to MARKETING.',
																	'arraycodes-order-notifications-woocommerce'
																) }{ ' ' }
																<ExternalLink
																	href="https://business.facebook.com/wa/manage/message-templates/"
																	style={ {
																		color: '#7d4e00',
																		fontWeight:
																			'600',
																	} }
																>
																	{ __(
																		'Appeal on Meta Business Suite',
																		'arraycodes-order-notifications-woocommerce'
																	) }
																</ExternalLink>
															</span>
														</div>
													) }
												</>
											) }
										</td>
										<td>
											<div className="form-buttons">
												{ ! isHaveId ? (
													<Button
														onClick={ () =>
															handleSentNewOrderCustomer(
																fields
															)
														}
														variant="secondary"
														disabled={ isSenting }
													>
														{ isSenting ? (
															<>
																<Spinner />
																{ __(
																	'Senting',
																	'arraycodes-order-notifications-woocommerce'
																) }
															</>
														) : (
															__(
																'Send to API',
																'arraycodes-order-notifications-woocommerce'
															)
														) }
													</Button>
												) : null }

												{ isHaveId ? (
													<>
														<Button
															onClick={ () =>
																handleSentUpdateOrderCustomer(
																	fields
																)
															}
															variant="secondary"
															disabled={
																isSenting
															}
														>
															{ isSenting ? (
																<>
																	<Spinner />
																	{ __(
																		'Senting',
																		'arraycodes-order-notifications-woocommerce'
																	) }
																</>
															) : (
																__(
																	'Update from API',
																	'arraycodes-order-notifications-woocommerce'
																)
															) }
														</Button>
														<Button
															onClick={ () =>
																handleSentDeleteOrderCustomer(
																	fields
																)
															}
															variant="secondary"
															disabled={
																isSenting
															}
														>
															{ isSenting ? (
																<>
																	<Spinner />
																	{ __(
																		'Senting',
																		'arraycodes-order-notifications-woocommerce'
																	) }
																</>
															) : (
																__(
																	'Exclude from API',
																	'arraycodes-order-notifications-woocommerce'
																)
															) }
														</Button>
													</>
												) : null }
											</div>
										</td>
									</tr>
								</tbody>
							</table>
							<div className="form-fields-button">
								<Button
									onClick={ openCreateMessage }
									variant="primary"
								>
									{ isHaveId
										? __(
												'Edit Message',
												'arraycodes-order-notifications-woocommerce'
										  )
										: __(
												'Create Message',
												'arraycodes-order-notifications-woocommerce'
										  ) }
								</Button>
							</div>
						</>
					) : (
						<>
							<div className="form-fields">
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									disabled={ isHaveId }
									label={ __(
										'Name',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={
										fields.whatsapp_template_new_order_customer_name
									}
									onChange={ ( newValue ) =>
										handleChange(
											newValue,
											'whatsapp_template_new_order_customer_name'
										)
									}
									help={ __(
										'Name your message template',
										'arraycodes-order-notifications-woocommerce'
									) }
								/>
								<SelectControl
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									disabled={ isHaveId }
									label={ __(
										'Languages',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={
										fields.whatsapp_template_new_order_customer_language
									}
									onChange={ ( newValue ) =>
										handleChange(
											newValue,
											'whatsapp_template_new_order_customer_language'
										)
									}
									options={ [
										{
											label: 'Afrikaans',
											value: 'af',
										},
										{
											label: 'Albanian',
											value: 'sq',
										},
										{
											label: 'Arabic',
											value: 'ar',
										},
										{
											label: 'Azerbaijani',
											value: 'az',
										},
										{
											label: 'Bengali',
											value: 'bn',
										},
										{
											label: 'Bulgarian',
											value: 'bg',
										},
										{
											label: 'Catalan',
											value: 'ca',
										},
										{
											label: 'Chinese (CHN)',
											value: 'zh_CN',
										},
										{
											label: 'Chinese (HKG)',
											value: 'zh_HK',
										},
										{
											label: 'Chinese (TAI)',
											value: 'zh_TW',
										},
										{
											label: 'Croatian',
											value: 'hr',
										},
										{
											label: 'Czech',
											value: 'cs',
										},
										{
											label: 'Danish',
											value: 'da',
										},
										{
											label: 'Dutch',
											value: 'nl',
										},
										{
											label: 'English',
											value: 'en',
										},
										{
											label: 'English (UK)',
											value: 'en_GB',
										},
										{
											label: 'English (US)',
											value: 'en_US',
										},
										{
											label: 'Estonian',
											value: 'et',
										},
										{
											label: 'Filipino',
											value: 'fil',
										},
										{
											label: 'Finnish',
											value: 'fi',
										},
										{
											label: 'French',
											value: 'fr',
										},
										{
											label: 'Georgian',
											value: 'ka',
										},
										{
											label: 'German',
											value: 'de',
										},
										{
											label: 'Greek',
											value: 'el',
										},
										{
											label: 'Gujarati',
											value: 'gu',
										},
										{
											label: 'Hausa',
											value: 'ha',
										},
										{
											label: 'Hebrew',
											value: 'he',
										},
										{
											label: 'Hindi',
											value: 'hi',
										},
										{
											label: 'Hungarian',
											value: 'hu',
										},
										{
											label: 'Indonesian',
											value: 'id',
										},
										{
											label: 'Irish',
											value: 'ga',
										},
										{
											label: 'Italian',
											value: 'it',
										},
										{
											label: 'Japanese',
											value: 'ja',
										},
										{
											label: 'Kannada',
											value: 'kn',
										},
										{
											label: 'Kazakh',
											value: 'kk',
										},
										{
											label: 'Kinyarwanda',
											value: 'rw_RW',
										},
										{
											label: 'Korean',
											value: 'ko',
										},
										{
											label: 'Kyrgyz (Kyrgyzstan)',
											value: 'ky_KG',
										},
										{
											label: 'Lao',
											value: 'lo',
										},
										{
											label: 'Latvian',
											value: 'lv',
										},
										{
											label: 'Lithuanian',
											value: 'lt',
										},
										{
											label: 'Macedonian',
											value: 'mk',
										},
										{
											label: 'Malay',
											value: 'ms',
										},
										{
											label: 'Malayalam',
											value: 'ml',
										},
										{
											label: 'Marathi',
											value: 'mr',
										},
										{
											label: 'Norwegian',
											value: 'nb',
										},
										{
											label: 'Persian',
											value: 'fa',
										},
										{
											label: 'Polish',
											value: 'pl',
										},
										{
											label: 'Portuguese (BR)',
											value: 'pt_BR',
										},
										{
											label: 'Portuguese (POR)',
											value: 'pt_PT',
										},
										{
											label: 'Punjabi',
											value: 'pa',
										},
										{
											label: 'Romanian',
											value: 'ro',
										},
										{
											label: 'Russian',
											value: 'ru',
										},
										{
											label: 'Serbian',
											value: 'sr',
										},
										{
											label: 'Slovak',
											value: 'sk',
										},
										{
											label: 'Slovenian',
											value: 'sl',
										},
										{
											label: 'Spanish',
											value: 'es',
										},
										{
											label: 'Spanish (ARG)',
											value: 'es_AR',
										},
										{
											label: 'Spanish (SPA)',
											value: 'es_ES',
										},
										{
											label: 'Spanish (MEX)',
											value: 'es_MX',
										},
										{
											label: 'Swahili',
											value: 'sw',
										},
										{
											label: 'Swedish',
											value: 'sv',
										},
										{
											label: 'Tamil',
											value: 'ta',
										},
										{
											label: 'Telugu',
											value: 'te',
										},
										{
											label: 'Thai',
											value: 'th',
										},
										{
											label: 'Turkish',
											value: 'tr',
										},
										{
											label: 'Ukrainian',
											value: 'uk',
										},
										{
											label: 'Urdu',
											value: 'ur',
										},
										{
											label: 'Uzbek',
											value: 'uz',
										},
										{
											label: 'Vietnamese',
											value: 'vi',
										},
										{
											label: 'Zulu',
											value: 'zu',
										},
									] }
									help={ __(
										'Choose languages for your message template. You can delete or add more languages later',
										'arraycodes-order-notifications-woocommerce'
									) }
								/>
								<SelectControl
									__nextHasNoMarginBottom
									__next40pxDefaultSize
									label={ __(
										'Header Type',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={
										fields.whatsapp_template_new_order_customer_header_type
									}
									onChange={ ( newValue ) =>
										handleChange(
											newValue,
											'whatsapp_template_new_order_customer_header_type'
										)
									}
									options={ [
										{
											label: 'None',
											value: 'none',
										},
										{
											label: 'Text',
											value: 'text',
										},
										{
											label: 'Image',
											value: 'image',
										},
									] }
								/>
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __(
										'Header (Optional)',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={
										fields.whatsapp_template_new_order_customer_header
									}
									onChange={ ( newValue ) =>
										handleChange(
											newValue,
											'whatsapp_template_new_order_customer_header'
										)
									}
									help={ getHeaderHelp( headerType ) }
									className="components-base-control-bottom-minus-active"
								/>
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __(
										'Footer (Optional)',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={
										fields.whatsapp_template_new_order_customer_footer
									}
									onChange={ ( newValue ) =>
										handleChange(
											newValue,
											'whatsapp_template_new_order_customer_footer'
										)
									}
									help={ __(
										'Add a short line of text to the bottom of your message template',
										'arraycodes-order-notifications-woocommerce'
									) }
								/>
								<TextareaControl
									__nextHasNoMarginBottom
									label={ __(
										'Body',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={
										fields.whatsapp_template_new_order_customer_body
									}
									onChange={ ( newValue ) =>
										handleChange(
											newValue,
											'whatsapp_template_new_order_customer_body'
										)
									}
									rows={ 10 }
									help={ __(
										"Enter the text for your message in the language you've selected",
										'arraycodes-order-notifications-woocommerce'
									) }
								/>
								<div className="add-variable">
									<Button
										variant="tertiary"
										onClick={ openVariableModal }
									>
										{ __(
											'Add variable',
											'arraycodes-order-notifications-woocommerce'
										) }
									</Button>
									<AddVariableModal
										isOpen={ isModalVariableOpen }
										onClose={ closeVariableModal }
										onSelectVariable={ handleSelectVariableFromParent }
									/>
								</div>

								<div className="form-buttons">
									<Button
										onClick={ closeCreateMessage }
										variant="secondary"
									>
										{ __(
											'Cancel',
											'arraycodes-order-notifications-woocommerce'
										) }
									</Button>

									<Button
										onClick={ handleSave }
										variant="primary"
										disabled={ isSaving }
									>
										{ isSaving ? (
											<>
												<Spinner />
												{ __(
													'Saving',
													'arraycodes-order-notifications-woocommerce'
												) }
											</>
										) : (
											__(
												'Save',
												'arraycodes-order-notifications-woocommerce'
											)
										) }
									</Button>
								</div>
							</div>
						</>
					) }
				</>
			) }
		</div>
	);
};
export default PageFormTemplateNewOrderCustomer;
