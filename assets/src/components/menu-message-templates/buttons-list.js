// ButtonsList.jsx
import { useState } from '@wordpress/element';
import {
	TextControl,
	Button,
	Card,
	CardBody,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ButtonAddOptions from './button-add-options';
import AddVariableModal from './add-variable-modal';

const ButtonsList = ( { value = [], onChange } ) => {
	const buttons = value || [];

	// Modal state
	const [ isModalOpen, setModalOpen ] = useState( false );
	const [ selectedButtonId, setSelectedButtonId ] = useState( null );

	const handleAddButton = ( type ) => {
		const newButton = {
			id: Date.now(),
			type,
			text: '',
			url: '',
			url_type: 'static',
			phone_number: '',
			code: '',
			flow_id: '',
		};

		// Default inteligente para visit_website
		if ( type === 'visit_website' ) {
			newButton.url = '{{site_url}}';
		}

		onChange( [ ...buttons, newButton ] );
	};

	const handleChange = ( id, field, val ) => {
		onChange(
			buttons.map( ( btn ) =>
				btn.id === id ? { ...btn, [ field ]: val } : btn
			)
		);
	};

	const handleUrlTypeChange = ( id, newType ) => {
		onChange(
			buttons.map( ( btn ) => {
				if ( btn.id !== id ) return btn;

				return {
					...btn,
					url_type: newType,
					url:
						newType === 'dynamic'
							? '{{site_url}}/{{customField}}'
							: '{{site_url}}',
				};
			} )
		);
	};


	const handleDelete = ( id ) => {
		onChange( buttons.filter( ( btn ) => btn.id !== id ) );
	};

	const openVariableModalForButton = ( buttonId ) => {
		setSelectedButtonId( buttonId );
		setModalOpen( true );
	};

	const closeModal = () => {
		setModalOpen( false );
		setSelectedButtonId( null );
	};

	// Insere variável diretamente no campo `url`
	const handleInsertVariable = ( variableCode ) => {
		if ( selectedButtonId == null ) return;

		const btn = buttons.find( ( b ) => b.id === selectedButtonId );
		if ( ! btn ) return;

		if ( btn.url?.includes( variableCode ) ) {
			closeModal();
			return;
		}

		handleChange(
			selectedButtonId,
			'url',
			( btn.url || '' ) + variableCode
		);
		closeModal();
	};

	return (
		<div className="button-list-items">
			<ButtonAddOptions onSelect={ handleAddButton } />

			{ buttons.map( ( btn ) => (
				<>
					<div className="button-list-item">
						<strong
							style={ {
								display: 'block',
								marginBottom: 8,
								marginTop: 24,
							} }
						>
							{ btn.type.replace( '_', ' ' ).toUpperCase() }
						</strong>

						{ /* Button text */ }
						<TextControl
							label={ __(
								'Button text',
								'arraycodes-order-notifications-woocommerce'
							) }
							value={ btn.text }
							onChange={ ( v ) =>
								handleChange( btn.id, 'text', v )
							}
						/>

						{ /* Visit website */ }
						{ btn.type === 'visit_website' && (
							<>
								<SelectControl
									label={ __(
										'URL type',
										'arraycodes-order-notifications-woocommerce'
									) }
									value={ btn.url_type }
									options={ [
										{
											label: __(
												'Static URL',
												'arraycodes-order-notifications-woocommerce'
											),
											value: 'static',
										},
										{
											label: __(
												'Dynamic URL',
												'arraycodes-order-notifications-woocommerce'
											),
											value: 'dynamic',
										},
									] }
									onChange={ ( v ) =>
										handleUrlTypeChange( btn.id, v )
									}
								/>

								<TextControl
									label={
										btn.url_type === 'static'
											? __(
													'Website URL',
													'arraycodes-order-notifications-woocommerce'
											  )
											: __(
													'Dynamic URL',
													'arraycodes-order-notifications-woocommerce'
											  )
									}
									help={
										btn.url_type === 'static'
											? __(
													'Default: {{site_url}}',
													'arraycodes-order-notifications-woocommerce'
											  )
											: __(
													'Default: {{site_url}}/{{customField}}',
													'arraycodes-order-notifications-woocommerce'
											  )
									}
									value={ btn.url }
									onChange={ ( v ) =>
										handleChange( btn.id, 'url', v )
									}
								/>

								{ /*{ btn.url_type === 'dynamic' && (*/ }
								{ /*	<Button*/ }
								{ /*		variant="tertiary"*/ }
								{ /*		onClick={ () =>*/ }
								{ /*			openVariableModalForButton( btn.id )*/ }
								{ /*		}*/ }
								{ /*	>*/ }
								{ /*		{ __(*/ }
								{ /*			'Add variable',*/ }
								{ /*			'arraycodes-order-notifications-woocommerce'*/ }
								{ /*		) }*/ }
								{ /*	</Button>*/ }
								{ /*) }*/ }
							</>
						) }

						{ /* Call phone / WhatsApp */ }
						{ ( btn.type === 'call_phone' ||
							btn.type === 'call_whatsapp' ) && (
							<TextControl
								label={ __(
									'Phone number',
									'arraycodes-order-notifications-woocommerce'
								) }
								value={ btn.phone_number }
								onChange={ ( v ) =>
									handleChange( btn.id, 'phone_number', v )
								}
							/>
						) }

						{ /* Copy code */ }
						{ btn.type === 'copy_code' && (
							<TextControl
								label={ __(
									'Offer code',
									'arraycodes-order-notifications-woocommerce'
								) }
								value={ btn.code }
								onChange={ ( v ) =>
									handleChange( btn.id, 'code', v )
								}
							/>
						) }

						{ /* Complete flow */ }
						{ btn.type === 'complete_flow' && (
							<TextControl
								label={ __(
									'Flow ID',
									'arraycodes-order-notifications-woocommerce'
								) }
								value={ btn.flow_id }
								onChange={ ( v ) =>
									handleChange( btn.id, 'flow_id', v )
								}
							/>
						) }

						<Button
							isDestructive
							variant="secondary"
							onClick={ () => handleDelete( btn.id ) }
							style={ { marginTop: 0 } }
						>
							{ __( 'Remove', 'arraycodes-order-notifications-woocommerce' ) }
						</Button>
					</div>
				</>
			) ) }

			{ /* Modal para inserir variáveis */ }
			<AddVariableModal
				isOpen={ isModalOpen }
				onClose={ closeModal }
				onSelectVariable={ handleInsertVariable }
				allowedVariables={ [ '{{site_url}}', '{{customField}}' ] }
			/>
		</div>
	);
};

export default ButtonsList;
