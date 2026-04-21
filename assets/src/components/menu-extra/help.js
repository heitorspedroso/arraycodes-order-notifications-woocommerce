/**
 * External dependencies
 */
import { SectionHeader } from '@woocommerce/components';
import {
	Card,
	CardHeader,
	CardBody,
	__experimentalHeading as Heading,
} from '@wordpress/components';
// eslint-disable-next-line no-duplicate-imports,@woocommerce/dependency-group
import { Flex, FlexItem, Button } from '@wordpress/components';
// eslint-disable-next-line @woocommerce/dependency-group
import { __ } from '@wordpress/i18n';

const PageHelp = () => {
	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__('Help', 'arraycodes-order-notifications-woocommerce')}
			/>
			<Flex gap="20" justify="flex-start">
				<FlexItem>
					<Card>
						<CardHeader>
							<Heading level={1}>
								{__(
									'Contact us',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Heading>
						</CardHeader>
						<CardBody>
							<Button
								variant="secondary"
								href="https://wordpress.org/support/plugin/arraycodes-order-notifications-woocommerce/"
								target="_blank"
							>
								{__(
									'Create ticket',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Button>
						</CardBody>
					</Card>
				</FlexItem>
				<FlexItem>
					<Card>
						<CardHeader>
							<Heading level={1}>
								{__(
									'Configuration',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Heading>
						</CardHeader>
						<CardBody>
							<Button
								variant="secondary"
								href="https://wordpress.org/plugins/notifications-with-whatsapp/#installation"
								target="_blank"
							>
								{__(
									'View documentation',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Button>
						</CardBody>
					</Card>
				</FlexItem>
				<FlexItem>
					<Card>
						<CardHeader>
							<Heading level={1}>
								{__(
									'Review',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Heading>
						</CardHeader>
						<CardBody>
							<Button
								variant="secondary"
								href="https://wordpress.org/support/plugin/arraycodes-order-notifications-woocommerce/#reviews"
								target="_blank"
							>
								{__(
									'Send your review',
									'arraycodes-order-notifications-woocommerce'
								)}
							</Button>
						</CardBody>
					</Card>
				</FlexItem>
			</Flex>
		</div>
	);
};
export default PageHelp;
