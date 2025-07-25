/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { InspectorControls, BlockControls, useBlockProps, HeadingLevelDropdown } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, RangeControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
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
        glossary,
        category
    } = attributes;

    const blockProps = useBlockProps();
    const [categorystate, setSelectedCategories] = useState([]);
    const [tagstate, setSelectedTags] = useState([]);
    const [idstate, setSelectedIDs] = useState([]);

    useEffect(() => {
        if (category) {
            const ids = category.split(',').map(id => parseInt(id, 10)).filter(id => !isNaN(id));
            setSelectedCategories(ids);
        }
        if (tag) {
            setSelectedTags(tag.split(','));
        }
        if (id) {
            const ids = id.split(',').map(i => parseInt(i, 10)).filter(i => !isNaN(i));
            setSelectedIDs(ids);
        }
    }, [category, tag, id]);

    const categories = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'glossary_category');
    }, []);

    const categoryoptions = [{ label: __('all', 'rrze-glossary'), value: '' }];
    if (Array.isArray(categories)) {
        categories.forEach((cat) => {
            categoryoptions.push({ label: cat.name, value: cat.id });
        });
    }

    const tags = useSelect((select) => {
        return select('core').getEntityRecords('taxonomy', 'glossary_tag');
    }, []);

    const tagoptions = [{ label: __('all', 'rrze-glossary'), value: '' }];
    if (Array.isArray(tags)) {
        tags.forEach(tag => {
            tagoptions.push({ label: tag.name, value: tag.slug });
        });
    }

    const glossarys = useSelect((select) => {
        return select('core').getEntityRecords('postType', 'glossary', { per_page: -1, orderby: 'title', order: "asc" });
    }, []);

    const glossaryoptions = [{ label: __('all', 'rrze-glossary'), value: 0 }];
    if (Array.isArray(glossarys)) {
        glossarys.forEach(glossary => {
            glossaryoptions.push({ label: glossary.title.rendered || __('No title', 'rrze-glossary'), value: glossary.id });
        });
    }

    const onChangeCategory = (newValues) => {
        const valueArray = Array.isArray(newValues) ? newValues.map(v => parseInt(v, 10)).filter(v => !isNaN(v)) : [parseInt(newValues, 10)];
        setSelectedCategories(valueArray);
        setAttributes({ category: valueArray.join(',') });
    };

    const onChangeTag = (newValues) => {
        const valueArray = Array.isArray(newValues) ? newValues : [newValues];
        setSelectedTags(valueArray);
        setAttributes({ tag: valueArray.join(',') });
    };

    const onChangeID = (newValues) => {
        const valueArray = Array.isArray(newValues) ? newValues.map(v => parseInt(v, 10)).filter(v => !isNaN(v)) : [parseInt(newValues, 10)];
        setSelectedIDs(valueArray);
        setAttributes({ id: valueArray.join(',') });
    };

    return (
        <>
            <BlockControls>
                <HeadingLevelDropdown
                    options={[2, 3, 4, 5, 6]}
                    value={hstart}
                    onChange={(value) => setAttributes({ hstart: value })}
                />
            </BlockControls>
            <InspectorControls>
                <PanelBody title={__('Filter options', 'rrze-glossary')} initialOpen={true}>
                    <SelectControl
                        label={__('Categories', 'rrze-glossary')}
                        help={__('Select categories to filter glossary entries.', 'rrze-glossary')}
                        value={categorystate}
                        options={categoryoptions}
                        onChange={onChangeCategory}
                        multiple
                    />
                    <SelectControl
                        label={__('Tags', 'rrze-glossary')}
                        help={__('Select tags to filter glossary entries.', 'rrze-glossary')}
                        value={tagstate}
                        options={tagoptions}
                        onChange={onChangeTag}
                        multiple
                    />
                    <SelectControl
                        label={__('Individual Glossary-Entries', 'rrze-glossary')}
                        help={__('Select glossary entries to filter glossary entries.', 'rrze-glossary')}
                        value={idstate}
                        options={glossaryoptions}
                        onChange={onChangeID}
                        multiple
                    />
                    <SelectControl
                        label={__('Language', 'rrze-glossary')}
                        help={__('Select language to filter glossary entries.', 'rrze-glossary')}
                        options={[
                            { label: __('all', 'rrze-faq'), value: '' },
                            { label: __('German', 'rrze-faq'), value: 'de' },
                            { label: __('English', 'rrze-faq'), value: 'en' },
                            { label: __('French', 'rrze-faq'), value: 'fr' },
                            { label: __('Spanish', 'rrze-faq'), value: 'es' },
                            { label: __('Russian', 'rrze-faq'), value: 'ru' },
                            { label: __('Chinese', 'rrze-faq'), value: 'zh' },
                        ]}
                        onChange={(value) => setAttributes({ lang: value })}
                    />
                    <SelectControl
                        label={__('Group by', 'rrze-glossary')}
                        help={__('Groups Glossary-Entries by category or tags.', 'rrze-glossary')}
                        options={[
                            { label: __('none', 'rrze-faq'), value: '' },
                            { label: __('Categories', 'rrze-faq'), value: 'category' },
                            { label: __('Tags', 'rrze-faq'), value: 'tag' },
                        ]}
                        onChange={(value) => setAttributes({ register: value })}
                    />
                </PanelBody>
                <PanelBody title={__('Styles', 'rrze-glossary')} initialOpen={false}>
                    <SelectControl
                        label={__('Glossary Style', 'rrze-glossary')}
                        help={__('Controls the Appearance of the Tab bar.', 'rrze-glossary')}
                        options={[
                            { label: __('A - Z', 'rrze-glossary'), value: 'a-z' },
                            { label: __('Tagcloud', 'rrze-glossary'), value: 'tagcloud' },
                            { label: __('Tabs', 'rrze-glossary'), value: 'tabs' },
                            { label: __('-- hidden --', 'rrze-glossary'), value: '' },
                        ]}
                        onChange={(value) => setAttributes({ registerstyle: value })}
                    />
                    {!glossary || glossary === 'none' ? (
                        <>
                            <ToggleControl
                                checked={!!hide_accordion}
                                label={__('Hide accordion', 'rrze-glossary')}
                                onChange={() => setAttributes({ hide_accordion: !hide_accordion })}
                            />
                            {!hide_accordion && (
                                <>
                                    <ToggleControl
                                        checked={!!expand_all_link}
                                        label={__('Show "expand all" button', 'rrze-glossary')}
                                        onChange={() => setAttributes({ expand_all_link: !expand_all_link })}
                                    />
                                    <ToggleControl
                                        checked={!!load_open}
                                        label={__('Load website with opened accordions', 'rrze-glossary')}
                                        onChange={() => setAttributes({ load_open: !load_open })}
                                    />
                                    <SelectControl
                                        label={__('Color', 'rrze-glossary')}
                                        options={["fau", "med", "nat", "phil", "rw", "tf"].map(c => ({ label: c, value: c }))}
                                        onChange={(value) => setAttributes({ color: value })}
                                    />
                                    <SelectControl
                                        label={__('Style', 'rrze-glossary')}
                                        options={["light", "dark"].map(s => ({ label: s, value: s })).concat([{ label: __('none', 'rrze-glossary'), value: '' }])}
                                        onChange={(value) => setAttributes({ style: value })}
                                    />
                                </>
                            )}
                            {hide_accordion && (
                                <ToggleControl
                                    checked={!!hide_title}
                                    label={__('Hide title', 'rrze-glossary')}
                                    onChange={() => setAttributes({ hide_title: !hide_title })}
                                />
                            )}
                        </>
                    ) : null}
                </PanelBody>
                <PanelBody title={__('Sorting options', 'rrze-glossary')} initialOpen={false}>
                    <SelectControl
                        label={__('Sort', 'rrze-glossary')}
                        options={[
                            { label: __('Title', 'rrze-glossary'), value: 'title' },
                            { label: __('ID', 'rrze-glossary'), value: 'id' },
                            { label: __('Sort field', 'rrze-glossary'), value: 'sortfield' },
                        ]}
                        onChange={(value) => setAttributes({ sort: value })}
                    />
                    <SelectControl
                        label={__('Order', 'rrze-glossary')}
                        options={[
                            { label: __('ASC', 'rrze-glossary'), value: 'ASC' },
                            { label: __('DESC', 'rrze-glossary'), value: 'DESC' },
                        ]}
                        onChange={(value) => setAttributes({ order: value })}
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
