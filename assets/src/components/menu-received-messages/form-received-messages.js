/**
 * External dependencies
 */
import {
	Button,
	Spinner,
	Icon,
	ExternalLink,
	TextareaControl,
} from '@wordpress/components';
import { arrowLeft } from '@wordpress/icons';
import { SectionHeader, Pagination } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, resolveSelect } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

const PageFormReceivedMessages = () => {
	const { createErrorNotice } = useDispatch( noticesStore );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ perPage, setPerPage ] = useState( 25 );
	const [ perPageKey, setPerPageKey ] = useState( 1 );
	const [ totalMessages, setTotalMessages ] = useState( 0 );

	const [ fields, setFields ] = useState( {
		whatsapp_received_messages: [],
		whatsapp_received_messages_by_id: [],
	} );

	const [ isSenting, setIsSenting ] = useState( false );
	const [ responseMessage, setResponseMessage ] = useState( '' );
	const handleChange = ( newMessage ) => {
		setResponseMessage( newMessage );
	};

	const onPageChange = ( newPage ) => {
		setPerPageKey( ( prevKey ) => prevKey + 1 );
		setCurrentPage( newPage );
	};
	const onPerPageChange = ( newPerPage ) => {
		setPerPageKey( ( prevKey ) => prevKey + 1 );
		setPerPage( newPerPage );
	};

	const [ waId, setWaId ] = useState( null );
	const [ isViewMessage, setViewMessage ] = useState( false );
	const openViewMessage = ( idMessage ) => {
		setPerPageKey( ( prevKey ) => prevKey + 1 );
		setViewMessage( true );
		setWaId( idMessage );
	};
	const dispatch = useDispatch();
	const closeViewMessage = () => {
		setViewMessage( false );
		setWaId( null );
		dispatch( 'shop-arraycodes-order-notifications-woocommerce' ).saveMessagesById( {
			whatsapp_received_messages_by_id: [],
		} );
	};

	const [ isLoading, setIsLoading ] = useState( true );

	useEffect( () => {
		if ( currentPage && perPage ) {
			setIsLoading( true );
			resolveSelect( 'shop-arraycodes-order-notifications-woocommerce' )
				.getMessages( currentPage, perPage, perPageKey )
				.then( ( fetchedFields ) => {
					setFields( ( prevFields ) => ( {
						...prevFields,
						...fetchedFields,
					} ) );
					setTotalMessages(
						fetchedFields.whatsapp_total_received_messages
					);
				} )
				.finally( () => {
					setIsLoading( false );
				} );
		}
		if ( isViewMessage && waId ) {
			setIsLoading( true );
			resolveSelect( 'shop-arraycodes-order-notifications-woocommerce' )
				.getMessagesById( waId, perPageKey )
				.then( ( fetchedFields ) => {
					setFields( ( prevFields ) => ( {
						...prevFields,
						...fetchedFields,
					} ) );
				} )
				.finally( () => {
					setIsLoading( false );
				} );
		}
	}, [ currentPage, perPage, perPageKey, isViewMessage, waId ] );

	useEffect( () => {
		// eslint-disable-next-line @typescript-eslint/no-shadow
		const waId = window.localStorage.getItem( 'waId' );
		if ( waId ) {
			setPerPageKey( ( prevKey ) => prevKey + 1 );
			setViewMessage( true );
			setWaId( waId );
			window.localStorage.removeItem( 'waId' );
		}
	}, [] );

	const shouldShowTextarea = () => {
		if ( fields.whatsapp_received_messages_by_id.length === 0 ) {
			return false;
		}

		const messages = fields.whatsapp_received_messages_by_id;

		const lastUserMessageIndex = messages.findLastIndex(
			( msg ) => msg.sender_type === 'user'
		);
		const lastUserMessage =
			lastUserMessageIndex !== -1
				? messages[ lastUserMessageIndex ]
				: null;

		if ( ! lastUserMessage ) {
			return false;
		}
		const messageDateTime = new Date( lastUserMessage.date_time );
		const currentTime = new Date();
		const timeDifference = currentTime - messageDateTime;

		const isRecent = timeDifference <= 24 * 60 * 60 * 1000;

		return isRecent;
	};

	const handleSetStorageAndReloadPage = () => {
		window.localStorage.setItem( 'activeComponent', 'item-9' );
		window.localStorage.setItem( 'waId', waId );
		window.location.reload();
	};

	// eslint-disable-next-line @typescript-eslint/no-shadow
	const handleSentResponseMessage = async ( responseMessage ) => {
		const dataToSend = {
			fields: {
				message: responseMessage,
				waId,
				conversationId:
					fields.whatsapp_received_messages_by_id[
						fields.whatsapp_received_messages_by_id.length - 1
					]?.conversation_id,
			},
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
				'/wp-json/notifications-with-whatsapp/v1/new-response-message',
				fetchOptions
			);

			if ( response.ok ) {
				const responseData = await response.json();
				// eslint-disable-next-line no-console
				// console.log( 'Server response:', responseData );
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
				title={__(
					'Received Messages',
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
					{!isViewMessage ? (
						<>
							<table className="wp-list-table widefat fixed striped table-view-list">
								<thead>
									<tr>
										<td>
											{__(
												'Name',
												'arraycodes-order-notifications-woocommerce'
											)}
										</td>
										<td>
											{__(
												'Number Phone',
												'arraycodes-order-notifications-woocommerce'
											)}
										</td>
										<td>
											{__(
												'Date',
												'arraycodes-order-notifications-woocommerce'
											)}
										</td>
										<td>
											{__(
												'Message',
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
								<tbody>
									{fields.whatsapp_received_messages.map(
										(message) => (
											// eslint-disable-next-line react/jsx-key
											<tr
												className={
													message.has_unread_user_message ===
													'1'
														? 'new-message'
														: ''
												}
												key={`${message.wa_id}-${message.date_time}`}
											>
												<td>{message.user_name}</td>
												<td>
													<ExternalLink
														href={
															'https://wa.me/' +
															message.wa_id
														}
													>
														{message.wa_id}
													</ExternalLink>
												</td>
												<td>
													{new Date(
														message.date_time
													).toLocaleString()}
												</td>
												<td>
													{message.message.length > 10
														? `${message.message.substring(
																0,
																10
															)}...`
														: message.message}
												</td>
												<td>
													<div className="form-buttons">
														<Button
															onClick={() => {
																openViewMessage(
																	message.wa_id
																);
															}}
															variant="secondary"
														>
															{__(
																'View',
																'arraycodes-order-notifications-woocommerce'
															)}
														</Button>
													</div>
												</td>
											</tr>
										)
									)}
								</tbody>
							</table>
							<div className={'pagination'}>
								<Pagination
									onPageChange={onPageChange}
									onPerPageChange={onPerPageChange}
									page={currentPage}
									perPage={perPage}
									total={totalMessages}
								/>
							</div>
						</>
					) : (
						<>
							<Icon
								icon={arrowLeft}
								size={36}
								onClick={closeViewMessage}
								className="back-view-message-icon"
							/>
							<table className="wp-list-table widefat fixed striped table-view-list response-messages-table">
								<thead>
									{fields.whatsapp_received_messages_by_id
										.length > 0 &&
										fields.whatsapp_received_messages_by_id.map(
											(message, index) => (
												<tr key={index}>
													<td
														colSpan={2}
														align={'center'}
													>
														{message.user_name}
														{' - '}
														<ExternalLink
															href={
																'https://wa.me/' +
																message.wa_id
															}
														>
															{message.wa_id}
														</ExternalLink>
													</td>
												</tr>
											)
										)[0]}
								</thead>
							</table>
							<div className="div-message">
								{fields.whatsapp_received_messages_by_id.map(
									// eslint-disable-next-line @typescript-eslint/no-unused-vars
									(message, index) => (
										// eslint-disable-next-line react/jsx-key
										<div
											className={
												message.sender_type === 'system'
													? 'box-message system'
													: 'box-message user'
											}
											key={index}
										>
											<div className="div-message-text">
												{message.reaction && (
													<div className="div-message-reaction">
														{message.reaction}
													</div>
												)}
												{message.message_type ===
												'image' ? (
													<img
														src={message.message}
														alt="User-submitted image"
														style={{
															maxWidth: '100%',
															borderRadius: '5px',
														}}
														loading="lazy"
													/>
												) : message.message_type ===
												  'audio' ? (
													<audio
														controls
														style={{
															maxWidth: '100%',
														}}
													>
														<source
															src={
																message.message
															}
															type="audio/ogg"
														/>
													</audio>
												) : message.message_type ===
												  'video' ? (
													<video
														controls
														style={{
															maxWidth: '100%',
															borderRadius: '5px',
														}}
													>
														<source
															src={
																message.message
															}
															type="video/mp4"
														/>
													</video>
												) : message.message_type ===
												  'document' ? (
													<a
														href={message.message}
														target="_blank"
														rel="noreferrer"
														style={{
															display: 'flex',
															alignItems:
																'center',
															gap: '6px',
															textDecoration:
																'none',
														}}
													>
														<span
															style={{
																fontSize:
																	'20px',
															}}
														>
															📄
														</span>
														<span>
															{message.message
																.split('/')
																.pop()}
														</span>
													</a>
												) : (
													message.message
												)}
											</div>
											<span>
												{new Intl.DateTimeFormat(
													'en-US',
													{
														dateStyle: 'short',
														timeStyle: 'short',
														hourCycle: 'h23',
													}
												)
													.format(
														new Date(
															message.date_time
														)
													)
													.replace(', ', ' - ')}
												{message.sender_type ===
													'system' && (
													<div
														className={
															'whatsapp-checks status-' +
															message.status
														}
													></div>
												)}
											</span>
										</div>
									)
								)}
							</div>
							<div className={'response-chat'}>
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
										<TextareaControl
											__nextHasNoMarginBottom
											value={responseMessage}
											onChange={handleChange}
											rows={2}
											label={__(
												'Enter your message',
												'arraycodes-order-notifications-woocommerce'
											)}
											disabled
										/>
										<Button
											onClick={() =>
												handleSentResponseMessage(
													responseMessage
												)
											}
											variant="secondary"
											disabled
										>
											{isSenting ? (
												<>
													<Spinner />
													{__(
														'Senting',
														'arraycodes-order-notifications-woocommerce'
													)}
												</>
											) : (
												__(
													'Sent',
													'arraycodes-order-notifications-woocommerce'
												)
											)}
										</Button>
									</div>
								</>
							</div>
						</>
					)}
				</>
			)}
		</div>
	);
};
export default PageFormReceivedMessages;
