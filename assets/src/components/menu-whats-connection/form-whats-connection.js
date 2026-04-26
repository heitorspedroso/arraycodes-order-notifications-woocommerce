/**
 * External dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	Button,
	TextControl,
	Spinner,
	ToggleControl,
	Tip,
	ExternalLink,
} from '@wordpress/components';
import { store as noticesStore } from '@wordpress/notices';
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const PageFormWhatsConnection = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );

	const dispatch = useDispatch();

	const [ isSaving, setIsSaving ] = useState( false );
	const [ isTokenisValid, setIsTokenisValid ] = useState( null );
	const [ isWabaIdisValid, setIsWabaIdisValid ] = useState( null );
	const [ isAppSecretValid, setIsAppSecretValid ] = useState( null );
	const [ phoneNumberId, setPhoneNumberId ] = useState( '' );

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

	const [ fields, setFields ] = useState( {
		whatsapp_api_log: '',
		whatsapp_api_token: '',
		whatsapp_api_app_secret: '',
		whatsapp_api_phone_number: '',
		whatsapp_api_account_id: '',
		whatsapp_api_phone_number_to: '',
		whatsapp_api_checked_input: '',
	} );

	const handleChange = useCallback( ( newText, fieldKey ) => {
		if ( fieldKey === 'whatsapp_api_token' ) {
			setIsTokenisValid( null );
		}
		if ( fieldKey === 'whatsapp_api_account_id' ) {
			setIsWabaIdisValid( null );
		}
		if ( fieldKey === 'whatsapp_api_phone_number' ) {
			setPhoneNumberId( '' );
		}
		if ( fieldKey === 'whatsapp_api_app_secret' ) {
			setIsAppSecretValid( null );
		}

		setFields( ( prevFields ) => ( {
			...prevFields,
			[ fieldKey ]: newText,
		} ) );
	}, [] );

	const getTokenisValid = useCallback( async () => {
		try {
			// eslint-disable-next-line @typescript-eslint/no-shadow
			const isTokenisValid = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/debug-token',
			} );
			setIsTokenisValid( isTokenisValid.message );
		} catch ( error ) {
			//console.error( 'Erro ao buscar o Webhook:', error );
			setIsTokenisValid( false );
		}
	}, [] );

	const getWabaIdisValid = useCallback( async () => {
		try {
			// eslint-disable-next-line @typescript-eslint/no-shadow
			const isWabaIdisValid = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/debug-waba-id',
			} );
			setIsWabaIdisValid( isWabaIdisValid.message );
			setPhoneNumberId( isWabaIdisValid.data.id );
		} catch ( error ) {
			//console.error( 'Erro ao buscar o Webhook:', error );
			setIsWabaIdisValid( false );
		}
	}, [] );

	const getAppSecretIsValid = useCallback( async () => {
		try {
			// eslint-disable-next-line @typescript-eslint/no-shadow
			const isAppSecretValid = await apiFetch( {
				method: 'GET',
				path: '/arraycodes-order-notifications-woocommerce/v1/debug-app-secret',
			} );
			setIsAppSecretValid( isAppSecretValid.message );
		} catch ( error ) {
			setIsAppSecretValid( false );
		}
	}, [] );

	const handleSave = async () => {
		try {
			setIsSaving( true );

			await dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveSettings(
				{
					whatsapp_api_log: fields.whatsapp_api_log,
					whatsapp_api_token: fields.whatsapp_api_token,
					whatsapp_api_phone_number: fields.whatsapp_api_phone_number,
					whatsapp_api_account_id: fields.whatsapp_api_account_id,
					whatsapp_api_phone_number_to:
						fields.whatsapp_api_phone_number_to,
					whatsapp_api_checked_input:
						fields.whatsapp_api_checked_input,
					whatsapp_api_app_secret: fields.whatsapp_api_app_secret,
				},
				fieldsFromStore
			);

			setIsSaving( false );

			createSuccessNotice(
				__( 'Settings saved!', 'arraycodes-order-notifications-woocommerce' ),
				{ type: 'snackbar' }
			);
		} catch ( error ) {
			console.error( 'Error saving settings:', error );
			createErrorNotice( 'An error occurred while saving settings.', {
				type: 'snackbar',
			} );
		}
	};

	useEffect( () => {
		if ( fieldsFromStore ) {
			setFields( fieldsFromStore );

			if ( fieldsFromStore.whatsapp_api_token !== '' ) {
				getTokenisValid();
			}

			if ( fieldsFromStore.whatsapp_api_account_id !== '' ) {
				getWabaIdisValid();
			}

			if ( fieldsFromStore.whatsapp_api_app_secret !== '' ) {
				getAppSecretIsValid();
			}
		}
	}, [ fieldsFromStore, getTokenisValid, getWabaIdisValid, getAppSecretIsValid ] );

	const whatsappApiLogHelpText = (
		<>
			{ __(
				'Enable to record a log of communication between the store and the WhatsApp Cloud API on each purchase via WhatsApp',
				'arraycodes-order-notifications-woocommerce'
			) }
		</>
	);
	const whatsappCheckedInputHelpText = (
		<>
			{ __(
				'Enable to check as default the input: I want to receive order notifications via WhatsApp in checkout',
				'arraycodes-order-notifications-woocommerce'
			) }
		</>
	);
	const whatsappApiPhoneNumberToHelpText = (
		<>
			{ __(
				'Enter the phone number to which the WhatsApp message will be sent to the Seller. Example: 5511999999999',
				'arraycodes-order-notifications-woocommerce'
			) }
		</>
	);

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={ __(
					'WhatsApp Connection Settings Cloud API Credentials',
					'arraycodes-order-notifications-woocommerce'
				) }
			/>
			<div className="form-legend">
				<Tip>
					{ __(
						'Enter your WhatsApp Cloud API credentials to connect your store and automatically send messages that have been configured in Message Templates',
						'arraycodes-order-notifications-woocommerce'
					) }
					<br />
					{ __(
						'Learn how to access your',
						'arraycodes-order-notifications-woocommerce'
					) }{ ' ' }
					<ExternalLink href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started">
						WhatsApp Cloud API credentials
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
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Enable Log Cloud API',
							'arraycodes-order-notifications-woocommerce'
						) }
						checked={ fields.whatsapp_api_log }
						onChange={ ( newValue ) =>
							handleChange( newValue, 'whatsapp_api_log' )
						}
						help={ whatsappApiLogHelpText }
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'Access token',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ fields.whatsapp_api_token }
						onChange={ ( newValue ) =>
							handleChange( newValue, 'whatsapp_api_token' )
						}
						className={
							// eslint-disable-next-line no-nested-ternary
							isTokenisValid === false
								? 'invalid'
								: isTokenisValid === true
								? 'valid'
								: ''
						}
						help={
							// eslint-disable-next-line no-nested-ternary
							isTokenisValid === false ? (
								<>
									{ __(
										'Invalid token',
										'arraycodes-order-notifications-woocommerce'
									) }
									<br />
									<span>
										{ __(
											'Check if the token was entered correctly or generate a new one.',
											'arraycodes-order-notifications-woocommerce'
										) }{ ' ' }
										<ExternalLink href="https://woocommerce.com/document/notifications-with-whatsapp/#creating-a-permanent-access-token-configuration-meta-business">
											Read more
										</ExternalLink>
									</span>
								</>
							) : isTokenisValid === true ? (
								<>
									{ __(
										'Valid token',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							) : (
								<>
									{ __(
										'Enter your API access token from WhatsApp',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							)
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="password"
						label={ __(
							'App Secret',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ fields.whatsapp_api_app_secret }
						onChange={ ( newValue ) =>
							handleChange( newValue, 'whatsapp_api_app_secret' )
						}
						className={
							// eslint-disable-next-line no-nested-ternary
							isAppSecretValid === false
								? 'invalid'
								: isAppSecretValid === true
								? 'valid'
								: ''
						}
						help={
							// eslint-disable-next-line no-nested-ternary
							isAppSecretValid === false ? (
								<>
									{ __(
										'Invalid App Secret',
										'arraycodes-order-notifications-woocommerce'
									) }
									<br />
									<span>
										{ __(
											'Check if the App Secret was entered correctly.',
											'arraycodes-order-notifications-woocommerce'
										) }{ ' ' }
										<ExternalLink href="https://developers.facebook.com/apps/">
											{ __(
												'Open Meta Developers',
												'arraycodes-order-notifications-woocommerce'
											) }
										</ExternalLink>
									</span>
								</>
							) : isAppSecretValid === true ? (
								<>
									{ __(
										'Valid App Secret',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							) : (
								<>
									{ __(
										'Your Meta App Secret (App Settings → Basic). Required for webhook signature validation.',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							)
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'Phone number ID',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ fields.whatsapp_api_phone_number }
						onChange={ ( newValue ) =>
							handleChange(
								newValue,
								'whatsapp_api_phone_number'
							)
						}
						className={
							// eslint-disable-next-line no-nested-ternary
							phoneNumberId === ''
								? ''
								: phoneNumberId ===
								  fields.whatsapp_api_phone_number
								? 'valid'
								: 'invalid'
						}
						help={
							// eslint-disable-next-line no-nested-ternary
							phoneNumberId === '' ? (
								<>
									{ __(
										'Enter your Phone number ID from WhatsApp',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							) : phoneNumberId ===
							  fields.whatsapp_api_phone_number ? (
								<>
									{ __(
										'Valid Phone number ID',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							) : (
								<>
									{ __(
										'Invalid Phone number ID',
										'arraycodes-order-notifications-woocommerce'
									) }
									<br />
									<span>
										{ __(
											'Check if the Phone number ID was entered correctly.',
											'arraycodes-order-notifications-woocommerce'
										) }{ ' ' }
										<ExternalLink href="https://woocommerce.com/document/notifications-with-whatsapp/#generate-whatsapp-cloud-api-credentials-configuration-meta-business">
											Read more
										</ExternalLink>
									</span>
								</>
							)
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'WhatsApp Business Account ID',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ fields.whatsapp_api_account_id }
						onChange={ ( newValue ) =>
							handleChange( newValue, 'whatsapp_api_account_id' )
						}
						className={
							// eslint-disable-next-line no-nested-ternary
							isWabaIdisValid === false
								? 'invalid'
								: isWabaIdisValid === true
								? 'valid'
								: ''
						}
						help={
							// eslint-disable-next-line no-nested-ternary
							isWabaIdisValid === false ? (
								<>
									{ __(
										'Invalid WhatsApp Business Account ID',
										'arraycodes-order-notifications-woocommerce'
									) }
									<br />
									<span>
										{ __(
											'Check if the WhatsApp Business Account ID was entered correctly.',
											'arraycodes-order-notifications-woocommerce'
										) }{ ' ' }
										<ExternalLink href="https://woocommerce.com/document/notifications-with-whatsapp/#generate-whatsapp-cloud-api-credentials-configuration-meta-business">
											Read more
										</ExternalLink>
									</span>
								</>
							) : isWabaIdisValid === true ? (
								<>
									{ __(
										'Valid WhatsApp Business Account ID',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							) : (
								<>
									{ __(
										'Enter your Live WhatsApp Business Account ID from WhatsApp',
										'arraycodes-order-notifications-woocommerce'
									) }
								</>
							)
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __(
							'Phone number To',
							'arraycodes-order-notifications-woocommerce'
						) }
						value={ fields.whatsapp_api_phone_number_to }
						onChange={ ( newValue ) =>
							handleChange(
								newValue,
								'whatsapp_api_phone_number_to'
							)
						}
						help={ whatsappApiPhoneNumberToHelpText }
					/>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __(
							'Enable check as default the input',
							'arraycodes-order-notifications-woocommerce'
						) }
						checked={ fields.whatsapp_api_checked_input }
						onChange={ ( newValue ) =>
							handleChange(
								newValue,
								'whatsapp_api_checked_input'
							)
						}
						help={ whatsappCheckedInputHelpText }
					/>
					<div className="form-buttons">
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
								__( 'Save', 'arraycodes-order-notifications-woocommerce' )
							) }
						</Button>
					</div>
				</>
			) }
		</div>
	);
};
export default PageFormWhatsConnection;
