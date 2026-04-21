/**
 * External dependencies
 */

import { Button } from '@wordpress/components';

const NavSection = ( { items, activeItem, onItemClick } ) => {
	return (
		<div className="custom-navigation">
			{ items.map( ( item ) => (
				<Button
					key={ item.item }
					onClick={ () => onItemClick( item.item ) }
					className={ `navigation-item-button ${
						activeItem === item.item ? 'is-active' : ''
					}` }
				>
					<span className="navigation-item-label">
						{ item.title }
					</span>
				</Button>
			) ) }
		</div>
	);
};

export default NavSection;
