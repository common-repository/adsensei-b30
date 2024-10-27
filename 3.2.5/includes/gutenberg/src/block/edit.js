/**
 * BLOCK: WP Adsensei
 *
 * Editor for WP Adsensei
 */
const { __ } = wp.i18n;
const {
	Component,
} = wp.element;
const {
	SelectControl,
} = wp.components;
class ADSENSEI extends Component {
	constructor() {
		super(...arguments);
	}
	render() {
		const { attributes, setAttributes } = this.props;
		const { selectedAdd } = attributes;
		const usedOptions = adsenseiGlobal.adsensei_get_ads.map((item) => {
			let item2 = item.replace(/Ad (\d+)/i, "[adsensei id=$1]");
			return { value: item2, label: item };
		});
		return (
			<div>
				<SelectControl
					label={__('Select add to Display ')}
					value={selectedAdd}
					options={usedOptions}
					onChange={(value) => {
						let toShortCode = value.replace(/Ad (\d+)/i, "[adsensei id=$1]");
						setAttributes({
							selectedAdd: toShortCode,
						});
					}}
				/>
			</div>
		);
	}
}
export default (ADSENSEI);
