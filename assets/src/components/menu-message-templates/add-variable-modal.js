import { Modal, Tip, Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function AddVariableModal( {
	isOpen,
	onClose,
	onSelectVariable,
	allowedVariables = null,
} ) {
	if ( ! isOpen ) return null;

	const allVariables = [
		{ code: '{{transactionID}}', label: 'Add transaction ID' },
		{ code: '{{transactionTax}}', label: 'Add transaction tax' },
		{ code: '{{transactionShipping}}', label: 'Add transaction shipping' },
		{ code: '{{transactionTotal}}', label: 'Add transaction Total' },
		{ code: '{{dateCreated}}', label: 'Add transaction date' },
		{ code: '{{firstName}}', label: 'Add first name' },
		{ code: '{{userName}}', label: 'Add user name' },
		{ code: '{{userPhone}}', label: 'Add user phone' },
		{ code: '{{userAddress}}', label: 'Add user address' },
		{ code: '{{products}}', label: 'Add products list' },
		{ code: '{{shippingName}}', label: 'Add shipping name' },
		{ code: '{{paymentName}}', label: 'Add payment name' },
		{
			code: '{{customField}}',
			label: 'Add custom field (See the doc to learn how to use this field)',
		},
		{ code: '{{couponCode}}', label: 'Add coupon code' },
		{ code: '{{productName}}', label: 'Add product name' },
	];

	const variables = allowedVariables
		? allVariables.filter( ( v ) => allowedVariables.includes( v.code ) )
		: allVariables;

	return (
		<Modal
			onRequestClose={ onClose }
			title="Add variable"
			shouldCloseOnEsc={ true }
			className="modal-add-variable"
		>
			<Tip>
				You can add variables in the text by clicking on the variable
				name
			</Tip>

			<div className="form-add-variable">
				{ variables.map( ( v, index ) => (
					<button
						key={ index }
						onClick={ () => {
							onSelectVariable( v.code );
							onClose();
						} }
					>
						<span className="variable-name">{ v.code }</span>
						<span className="variable-description">
							{ v.label }
						</span>
					</button>
				) ) }
			</div>
		</Modal>
	);
}
