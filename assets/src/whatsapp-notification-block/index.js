/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { Icon, comment } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import { Edit, Save } from './edit';
import metadata from './block.json';
registerBlockType( metadata, {
	icon: {
		src: <Icon icon={ comment } />,
	},
	edit: Edit,
	save: Save,
} );
