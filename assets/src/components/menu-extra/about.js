/**
 * External dependencies
 */
import { SectionHeader } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';

const About = () => {
	return (
		<div className="my-gutenberg-form">
			<SectionHeader
				title={__('About', 'arraycodes-order-notifications-woocommerce')}
			/>
			<p>
				1.0.0 - 2026-04-10
				<br />
				* New: Review Products notification message
				<br />
				* Support: Support -&gt; WP 6.9.4 WC 10.6.2
				<br />
				<br />
				<br />
			</p>
		</div>
	);
};
export default About;
