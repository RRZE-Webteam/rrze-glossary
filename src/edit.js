/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import {useEffect, useState} from '@wordpress/element';
import {useSelect} from '@wordpress/data';
import {InspectorControls, BlockControls, useBlockProps, HeadingLevelDropdown} from '@wordpress/block-editor';
import {PanelBody, TextControl, ToggleControl, SelectControl, RangeControl} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

function buildCategoryOptions(categories) {
	const map = new Map();
	const roots = [];

	categories.forEach((cat) => {
		cat.children = [];
		map.set(cat.id, cat);
	});

	categories.forEach((cat) => {
		if (cat.parent && map.has(cat.parent)) {
			map.get(cat.parent).children.push(cat);
		} else {
			roots.push(cat);
		}
	});

	const sortByName = (list) =>
		list.sort((a, b) =>
			a.name.localeCompare(b.name, undefined, { sensitivity: 'base' })
		);

	const flatten = (list, depth = 0) => {
		const result = [];
		sortByName(list).forEach((cat) => {
			result.push({
				label: `${'-'.repeat(depth)} ${cat.name}`.trim(),
				value: cat.slug,
			});
			result.push(...flatten(cat.children, depth + 1));
		});
		return result;
	};

	return flatten(roots);
}



export default function Edit({attributes, setAttributes}) {
    const {
        register,
        tag,
        id,
        hstart,
        order,
        sort,
        lang,
        additional_class,
        color,
        style,
        load_open,
        expand_all_link,
        hide_title,
        hide_accordion,
        registerstyle,
        glossary
    } = attributes;
    const blockProps = useBlockProps();
    const [categorystate, setSelectedCategories] = useState(['']);
    const [tagstate, setSelectedTags] = useState(['']);
    const [idstate, setSelectedIDs] = useState(['']);

    // useEffect(() => {
    //     setAttributes({
    //         register,
    //         tag,
    //         id,
    //         hstart,
    //         order,
    //         sort,
    //         lang,
    //         additional_class,
    //         color,
    //         style,
    //         load_open,
    //         expand_all_link,
    //         hide_title,
    //         hide_accordion,
    //         registerstyle,
    //         glossary
    //     });
    // }, [register, tag, id, hstart, order, sort, lang, additional_class, color, style, load_open, expand_all_link, hide_title, hide_accordion, registerstyle, glossary, setAttributes]);

	const categories = useSelect((select) => {
		return select('core').getEntityRecords('taxonomy', 'rrze_glossary_category', {
			per_page: -1,
			orderby: 'name',
			order: 'asc',
			status: 'publish',
			_fields: 'id,name,slug,parent',
		});
	}, []);


    const categoryoptions = [
        {
            label: __('all', 'rrze-glossary'),
            value: ''
        }
    ];

    if (Array.isArray(categories)) {
	    categoryoptions.push(...buildCategoryOptions(categories));
	}


    const tags = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'rrze_glossary_tag');
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
        return select('core').getEntityRecords('postType', 'rrze_glossary', {per_page: -1, orderby: 'title', order: "asc"});
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

    const registeroptions = [
        {
            label: __('none', 'rrze-faq'),
            value: ''
        },
        {
            label: __('Categories', 'rrze-faq'),
            value: 'category'
        },
        {
            label: __('Tags', 'rrze-faq'),
            value: 'tag'
        }
    ];


    const langoptions = [
        {
            label: __('all', 'rrze-faq'),
            value: ''
        },
        {
            label: __('German', 'rrze-faq'),
            value: 'de'
        },
        {

            label: __('English', 'rrze-faq'),
            value: 'en'
        },
        {

            label: __('French', 'rrze-faq'),
            value: 'fr'
        },
        {

            label: __('Spanish', 'rrze-faq'),
            value: 'es'
        },
        {
            label: __('Russian', 'rrze-faq'),
            value: 'ru'
        },
        {
            label: __('Chinese', 'rrze-faq'),
            value: 'zh'
        }
    ];

    const registerstyleoptions = [
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
        },
        {
            label: __('-- hidden --', 'rrze-glossary'),
            value: ''
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

    const styleoptions = [
        {
            label: __('none', 'rrze-glossary'),
            value: ''
        },
        {
            label: 'light',
            value: 'light'
        },
        {
            label: 'dark',
            value: 'dark'
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

    const onChangeCategory = (newValues) => {
        setSelectedCategories(newValues);
        setAttributes({category: String(newValues)})
    };

    const onChangeTag = (newValues) => {
        setSelectedTags(newValues);
        setAttributes({tag: String(newValues)})
    };

    const onChangeID = (newValues) => {
        setSelectedIDs(newValues);
        setAttributes({id: String(newValues)})
    };

    return (
        <>
            <BlockControls>
                <HeadingLevelDropdown
                    options={[2, 3, 4, 5, 6]}
                    value={hstart}
                    onChange={(value) => setAttributes({hstart: value})}
                />
            </BlockControls>
            <InspectorControls>
                <PanelBody title={__('Filter options', 'rrze-glossary')} initialOpen={true}>
                    <SelectControl
                        label={__(
                            "Categories",
                            'rrze-glossary'
                        )}
                        help={__('Select categories to filter glossary entries.', 'rrze-glossary')}
                        value={categorystate}
                        options={categoryoptions}
                        onChange={onChangeCategory}
                        multiple
                    />
                    <SelectControl
                        label={__(
                            "Tags",
                            'rrze-glossary'
                        )}
                        help={__('Select tags to filter glossary entries.', 'rrze-glossary')}
                        value={tagstate}
                        options={tagoptions}
                        onChange={onChangeTag}
                        multiple
                    />
                    <SelectControl
                        label={__(
                            "Individual Glossary-Entries",
                            'rrze-glossary'
                        )}
                        help={__('Select glossary entries to filter glossary entries.', 'rrze-glossary')}
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
                        help={__('Select language to filter glossary entries.', 'rrze-glossary')}
                        options={langoptions}
                        onChange={(value) => setAttributes({lang: value})}
                    />
                    <SelectControl
                        label={__(
                            "Group by",
                            'rrze-glossary'
                        )}
                        help={__('Groups Glossary-Entries by category or tags.', 'rrze-glossary')}
                        options={registeroptions}
                        onChange={(value) => setAttributes({register: value})}
                    />
                </PanelBody>
                <PanelBody title={__('Styles', 'rrze-glossary')} initialOpen={false}>
                    <SelectControl
                        label={__(
                            "Glossary Style",
                            'rrze-glossary'
                        )}
                        help={__('Controls the Appearance of the Tab bar.', 'rrze-glossary')}
                        options={registerstyleoptions}
                        onChange={(value) => setAttributes({registerstyle: value})}
                    />
                    {(!glossary || glossary === 'none') && (
                        <>
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
                            {!hide_accordion ? (
                                <>

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
                                        onChange={(value) => setAttributes({color: value})}
                                    />

                                    <SelectControl
                                        label={__('Style', 'rrze-glossary')}
                                        options={styleoptions}
                                        onChange={(value) => setAttributes({style: value})}
                                    />
                                </>
                            ) : (
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
                            )}
                        </>
                    )}
                </PanelBody>
                <PanelBody title={__('Sorting options', 'rrze-glossary')} initialOpen={false}>
                    <SelectControl
                        label={__(
                            "Sort",
                            'rrze-glossary'
                        )}
                        options={sortoptions}
                        onChange={(value) => setAttributes({sort: value})}
                    />
                    <SelectControl
                        label={__(
                            "Order",
                            'rrze-glossary'
                        )}
                        options={orderoptions}
                        onChange={(value) => setAttributes({order: value})}
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