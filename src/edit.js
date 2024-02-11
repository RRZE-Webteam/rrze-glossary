/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';


export default function Edit({ attributes, setAttributes }) {
	const { register, tag, id, hstart, order, sort, lang, additional_class, color, load_open, expand_all_link, hide_title, hide_accordion, glossarystyle, glossary } = attributes;
	const blockProps = useBlockProps();
	const [registerstate, setSelectedCategories] = useState(['']);
	const [tagstate, setSelectedTags] = useState(['']);
	const [idstate, setSelectedIDs] = useState(['']);

	useEffect(() => {
		setAttributes({ register: register });
		setAttributes({ tag: tag });
		setAttributes({ id: id });
		setAttributes({ hstart: hstart });
		setAttributes({ order: order });
		setAttributes({ sort: sort });
		setAttributes({ lang: lang });
		setAttributes({ additional_class: additional_class });
		setAttributes({ color: color });
		setAttributes({ load_open: load_open });
		setAttributes({ expand_all_link: expand_all_link });
		setAttributes({ hide_title: hide_title });
		setAttributes({ hide_accordion: hide_accordion });
		setAttributes({ glossarystyle: glossarystyle });
		setAttributes({ glossary: glossary });
	}, [register, tag, id, hstart, order, sort, lang, additional_class, color, load_open, expand_all_link, hide_title, hide_accordion, glossarystyle, glossary, setAttributes]);



	const categories = useSelect((select) => {
		return select('core').getEntityRecords('taxonomy', 'glossary_category');
	}, []);

	const registeroptions = [
		{
			label: __('all', 'rrze-glossary'),
			value: ''
		}
	];

	if (!!categories) {
		Object.values(categories).forEach(register => {
			registeroptions.push({
				label: register.name,
				value: register.slug,
			});
		});
	}

	const tags = useSelect((select) => {
		return select('core').getEntityRecords('taxonomy', 'glossary_tag');
	}, []);

	const tagoptions = [
		{
			label: __('all', 'rrze-glossary'),
			value: ''
		}
	];

	if (!!tags) {
		Object.values(tags).forEach(tag => {
			tagoptions.push({
				label: tag.name,
				value: tag.slug,
			});
		});
	}

	const glossarys = useSelect((select) => {
		return select('core').getEntityRecords('postType', 'glossary', { per_page: -1, orderby: 'title', order: "asc" });
	}, []);

	const glossaryoptions = [
		{
			label: __('all', 'rrze-glossary'),
			value: 0
		}
	];

	if (!!glossarys) {
		Object.values(glossarys).forEach(glossary => {
			glossaryoptions.push({
				label: glossary.title.rendered ? glossary.title.rendered : __('No title', 'rrze-glossary'),
				value: glossary.id,
			});
		});
	}

	// const languages = useSelect((select) => {
	// 	return select('core').getEntityRecords('term', 'lang', { per_page: -1 });
	// }, []);

	// console.log('edit.js languages: ' + JSON.stringify(languages));

	const langoptions = [
		{
			label: __('all', 'rrze-glossary'),
			value: ''
		}
	];

	// if (!!languages) {
	// 	Object.values(languages).forEach(language => {
	// 		langoptions.push({
	// 			label: language.name,
	// 			value: language.id,
	// 		});
	// 	});
	// }


	const glossarystyleoptions = [
		{
			label: __('-- hidden --', 'rrze-glossary'),
			value: ''
		},
		{
			label: __('A - Z', 'rrze-glossary'),
			value: 'a-z'
		},
		{
			label: __('Tagcloud', 'rrze-glossary'),
			value: 'tagcloud'
		},
		{
			label: __('Tabs', 'rrze-glossary'),
			value: 'tabs'
		}
	];

	const coloroptions = [
		{
			label: 'fau',
			value: 'fau'
		},
		{
			label: 'med',
			value: 'med'
		},
		{
			label: 'nat',
			value: 'nat'
		},
		{
			label: 'phil',
			value: 'phil'
		},
		{
			label: 'rw',
			value: 'rw'
		},
		{
			label: 'tf',
			value: 'tf'
		}
	];

	const sortoptions = [
		{
			label: __('Title', 'rrze-glossary'),
			value: 'title'
		},
		{
			label: __('ID', 'rrze-glossary'),
			value: 'id'
		},
		{
			label: __('Sort field', 'rrze-glossary'),
			value: 'sortfield'
		}
	];

	const orderoptions = [
		{
			label: __('ASC', 'rrze-glossary'),
			value: 'ASC'
		},
		{
			label: __('DESC', 'rrze-glossary'),
			value: 'DESC'
		}
	];

	// console.log('edit.js attributes: ' + JSON.stringify(attributes));

	const onChangeregister = (newValues) => {
		setSelectedCategories(newValues);
		setAttributes({ register: String(newValues) })
	};

	const onChangeTag = (newValues) => {
		setSelectedTags(newValues);
		setAttributes({ tag: String(newValues) })
	};

	const onChangeID = (newValues) => {
		setSelectedIDs(newValues);
		setAttributes({ id: String(newValues) })
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'rrze-glossary')}>
					<SelectControl
						label={__(
							"Categories",
							'rrze-glossary'
						)}
						value={registerstate}
						options={registeroptions}
						onChange={onChangeregister}
						multiple
					/>
					<SelectControl
						label={__(
							"Tags",
							'rrze-glossary'
						)}
						value={tagstate}
						options={tagoptions}
						onChange={onChangeTag}
						multiple
					/>
					<SelectControl
						label={__(
							"glossary",
							'rrze-glossary'
						)}
						value={idstate}
						options={glossaryoptions}
						onChange={onChangeID}
						multiple
					/>
					<SelectControl
						label={__(
							"Language",
							'rrze-glossary'
						)}
						options={langoptions}
						onChange={(value) => setAttributes({ lang: value })}
					/>

				</PanelBody>
			</InspectorControls>
			<InspectorControls group="styles">
				<PanelBody title={__('Styles', 'rrze-glossary')}>
					<SelectControl
						label={__(
							"Glossary Content",
							'rrze-glossary'
						)}
						options={glossaryoptions}
						onChange={(value) => setAttributes({ glossary: value })}
					/>
					<SelectControl
						label={__(
							"Glossary Style",
							'rrze-glossary'
						)}
						options={glossarystyleoptions}
						onChange={(value) => setAttributes({ glossarystyle: value })}
					/>
					<ToggleControl
						checked={!!hide_accordion}
						label={__(
							'Hide accordion',
							'rrze-glossary'
						)}
						onChange={() =>
							setAttributes({
								hide_accordion: !hide_accordion,
							})
						}
					/>
					<ToggleControl
						checked={!!hide_title}
						label={__(
							'Hide title',
							'rrze-glossary'
						)}
						onChange={() =>
							setAttributes({
								hide_title: !hide_title,
							})
						}
					/>
					<ToggleControl
						checked={!!expand_all_link}
						label={__(
							'Show "expand all" button',
							'rrze-glossary'
						)}
						onChange={() =>
							setAttributes({
								expand_all_link: !expand_all_link,
							})
						}
					/>
					<ToggleControl
						checked={!!load_open}
						label={__(
							'Load website with opened accordions',
							'rrze-glossary'
						)}
						onChange={() =>
							setAttributes({
								load_open: !load_open,
							})
						}
					/>
					<SelectControl
						label={__(
							"Color",
							'rrze-glossary'
						)}
						options={coloroptions}
						onChange={(value) => setAttributes({ color: value })}
					/>
					<TextControl
						label={__(
							"Additional CSS-class(es) for sourrounding DIV",
							'rrze-glossary'
						)}
						onChange={(value) => setAttributes({ additional_class: value })}
					/>
					<SelectControl
						label={__(
							"Sort",
							'rrze-glossary'
						)}
						options={sortoptions}
						onChange={(value) => setAttributes({ sort: value })}
					/>
					<SelectControl
						label={__(
							"Order",
							'rrze-glossary'
						)}
						options={orderoptions}
						onChange={(value) => setAttributes({ order: value })}
					/>
					<RangeControl
						label={__(
							"Heading starts with...",
							'rrze-glossary'
						)}
						onChange={(value) => setAttributes({ hstart: value })}
						min={2}
						max={6}
						initialPosition={2}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ServerSideRender
					block="create-block/rrze-glossary"
					attributes={attributes}
				/>
			</div>
		</>
	);
}