/**
 * External dependencies
 */
import {
	Button,
	Spinner,
	Icon,
	CheckboxControl, ExternalLink
} from '@wordpress/components';
import { arrowLeft } from '@wordpress/icons';
import { SectionHeader, Pagination } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, resolveSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';
import apiFetch from '@wordpress/api-fetch';

const PageFormStockNotifications = () => {
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ perPage, setPerPage ] = useState( 25 );
	const [ perPageKey, setPerPageKey ] = useState( 1 );
	const [ totalProducts, setTotalProducts ] = useState( 0 );
	const [ selectedItems, setSelectedItems ] = useState( [] );
	const [ selectAll, setSelectAll ] = useState( false );
	const [ detailPage, setDetailPage ] = useState( 1 );
	const [ detailPerPage, setDetailPerPage ] = useState( 25 );
	const [ detailTotal, setDetailTotal ] = useState( 0 );

	const [ fields, setFields ] = useState( {
		whatsapp_received_products_notify_me: [],
		whatsapp_received_products_notify_me_by_id: [],
	} );

	const [ isSenting, setIsSenting ] = useState( false );

	const onPageChange = ( newPage ) => {
		setPerPageKey( ( prevKey ) => prevKey + 1 );
		setCurrentPage( newPage );
	};
	const onPerPageChange = ( newPerPage ) => {
		setPerPageKey( ( prevKey ) => prevKey + 1 );
		setPerPage( newPerPage );
	};

	const [ productId, setProductId ] = useState( null );
	const [ isViewProduct, setViewProduct ] = useState( false );
	const openViewProduct = ( idProduct ) => {
		setPerPageKey( ( prevKey ) => prevKey + 1 );
		setViewProduct( true );
		setProductId( idProduct );
	};
	const dispatch = useDispatch();
	const closeViewProduct = () => {
		setViewProduct( false );
		setProductId( null );
		dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveMessagesById( {
			whatsapp_received_products_notify_me_by_id: [],
		} );
	};

	const [ isLoading, setIsLoading ] = useState( true );

	useEffect( () => {
		if ( currentPage && perPage ) {
			setIsLoading( true );
			resolveSelect( 'shop-arraycodes-order-notifications-woocommerce' )
				.getProductsNotifyMe( currentPage, perPage, perPageKey )
				.then( ( fetchedFields ) => {
					setFields( ( prevFields ) => ( {
						...prevFields,
						...fetchedFields,
					} ) );
					setTotalProducts(
						fetchedFields.whatsapp_total_received_products_notify_me
					);
				} )
				.finally( () => {
					setIsLoading( false );
				} );
		}
		if ( isViewProduct && productId ) {
			setIsLoading( true );
			resolveSelect( 'shop-arraycodes-order-notifications-woocommerce' )
				.getProductsNotifyMeById(
					productId,
					detailPage,
					detailPerPage,
					perPageKey
				)
				.then( ( fetchedFields ) => {
					setFields( ( prevFields ) => ( {
						...prevFields,
						...fetchedFields,
					} ) );
					setDetailTotal(
						fetchedFields.whatsapp_total_received_products_notify_me_by_id
					);
				} )
				.finally( () => {
					setIsLoading( false );
				} );
		}
	}, [ currentPage, detailPage, detailPerPage, perPageKey, isViewProduct, productId ] );

	useEffect( () => {
		// eslint-disable-next-line @typescript-eslint/no-shadow
		const productId = window.localStorage.getItem( 'productId' );
		if ( productId ) {
			setPerPageKey( ( prevKey ) => prevKey + 1 );
			setViewProduct( true );
			setProductId( productId );
			window.localStorage.removeItem( 'productId' );
		}
	}, [] );

	const handleSelectAll = ( isChecked ) => {
		setSelectAll( isChecked );
		if ( isChecked ) {
			const allIds = fields.whatsapp_received_products_notify_me_by_id.map(
				( item ) => item.id
			);
			setSelectedItems( allIds );
		} else {
			setSelectedItems( [] );
		}
	};

	const handleSelectItem = ( productId, isChecked ) => {
		if ( isChecked ) {
			setSelectedItems( ( prev ) => [ ...prev, productId ] );
		} else {
			setSelectedItems( ( prev ) => prev.filter( ( id ) => id !== productId ) );
		}
	};

	const handleSendNotification = () => {
		if ( selectedItems.length === 0 ) {
			createErrorNotice( __( 'Select at least one contact.', 'arraycodes-order-notifications-woocommerce' ), {
				explicitDismiss: true,
				type: 'snackbar',
				icon: '⛔',
			} );
			return;
		}

		setIsSenting( true );

		apiFetch( {
			path: '/arraycodes-order-notifications-woocommerce/v1/send-stock-notification',
			method: 'POST',
			data: {
				requests: selectedItems,
			},
		} )
			.then( ( response ) => {
				// console.log('response',response);
				setSelectedItems( [] );
				setSelectAll( false );
				handleSetStorageAndReloadPage();
			} )
			.catch( ( error ) => {
				createErrorNotice( __( 'Error sending notifications.', 'arraycodes-order-notifications-woocommerce' ), {
					explicitDismiss: true,
					type: 'snackbar',
					icon: '⛔',
				} );
			} )
			.finally( () => {
				setIsSenting( false );
			} );
	};

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-11' );
		window.localStorage.setItem( 'productId', productId );
		window.location.reload();
	};

	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__(
					'In-stock item notification requests',
					'arraycodes-order-notifications-woocommerce'
				)}
			/>
			{isLoading ? (
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
											'Product',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td style={{ width: 150 }}>
										{__(
											'Interested',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
									<td style={{ width: 150 }}>
										{__(
											'Actions',
											'arraycodes-order-notifications-woocommerce'
										)}
									</td>
								</tr>
							</thead>
						</table>
					</div>
				</>
			)}
		</div>
	);
};
export default PageFormStockNotifications;
