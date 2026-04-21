import { Button, Dropdown, MenuGroup, MenuItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const ButtonAddOptions = ( { onSelect } ) => {
	return (
		<>
			<div className="components-base-control no-margin-bottom">
				<div className="components-base-control__field">
					<label
						className="components-base-control__label css-2o4jwd ej5x27r2"
						htmlFor=""
					>
						{ __(
							'BUTTONS (Optional)',
							'arraycodes-order-notifications-woocommerce'
						) }
					</label>
				</div>
			</div>
			<Dropdown
				position="bottom left"
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Button variant="secondary" onClick={ onToggle }>
						{ __( 'Add button', 'arraycodes-order-notifications-woocommerce' ) }
					</Button>
				) }
				renderContent={ ( { onClose } ) => (
					<MenuGroup>
						{ /*<MenuItem*/ }
						{ /*	onClick={ () => {*/ }
						{ /*		onSelect( 'custom' );*/ }
						{ /*		onClose();*/ }
						{ /*	} }*/ }
						{ /*>*/ }
						{ /*	Custom*/ }
						{ /*</MenuItem>*/ }

						<MenuItem
							onClick={ () => {
								onSelect( 'visit_website' );
								onClose();
							} }
						>
							{ __(
								'Visit website',
								'arraycodes-order-notifications-woocommerce'
							) }
						</MenuItem>

						{ /*<MenuItem*/ }
						{ /*	onClick={ () => {*/ }
						{ /*		onSelect( 'call_phone' );*/ }
						{ /*		onClose();*/ }
						{ /*	} }*/ }
						{ /*>*/ }
						{ /*	Call phone number*/ }
						{ /*</MenuItem>*/ }

						{ /*<MenuItem*/ }
						{ /*	onClick={ () => {*/ }
						{ /*		onSelect( 'copy_code' );*/ }
						{ /*		onClose();*/ }
						{ /*	} }*/ }
						{ /*>*/ }
						{ /*	Copy offer code*/ }
						{ /*</MenuItem>*/ }

						{ /*<MenuItem*/ }
						{ /*	onClick={ () => {*/ }
						{ /*		onSelect( 'complete_flow' );*/ }
						{ /*		onClose();*/ }
						{ /*	} }*/ }
						{ /*>*/ }
						{ /*	Complete flow*/ }
						{ /*</MenuItem>*/ }

						{ /*<MenuItem*/ }
						{ /*	onClick={ () => {*/ }
						{ /*		onSelect( 'call_whatsapp' );*/ }
						{ /*		onClose();*/ }
						{ /*	} }*/ }
						{ /*>*/ }
						{ /*	Call via WhatsApp*/ }
						{ /*</MenuItem>*/ }
					</MenuGroup>
				) }
			/>
		</>
	);
};

export default ButtonAddOptions;
