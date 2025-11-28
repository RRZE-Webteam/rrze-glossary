<?php

namespace RRZE\Glossary;

defined('ABSPATH') || exit;

use RRZE\Glossary\API;
use function RRZE\Glossary\Config\getConstants;


/**
 * Layout settings for "glossary"
 */
class Layout
{

    public function __construct()
    {

        add_filter('pre_get_posts', [$this, 'makeGlossarySortable']);
        // show content in box if not editable ( = source is not "website" )
        add_action('admin_menu', [$this, 'toggleEditor']);
        // Table "All glossaries"
        add_filter('manage_glossary_posts_columns', [$this, 'addGlossaryColumns']);
        add_action('manage_glossary_posts_custom_column', [$this, 'getGlossaryColumnsValues'], 10, 2);
        add_filter('manage_edit-glossary_sortable_columns', [$this, 'addGlossarySortableColumns']);
        add_action('restrict_manage_posts', [$this, 'addGlossaryFilters'], 10, 1);

        // Table "Category"
        add_filter('manage_edit-glossary_category_columns', [$this, 'addTaxColumns']);
        add_filter('manage_glossary_category_custom_column', [$this, 'getTaxColumnsValues'], 10, 3);
        add_filter('manage_edit-glossary_category_sortable_columns', [$this, 'addTaxColumns']);
        // Table "Tags"
        add_filter('manage_edit-glossary_tag_columns', [$this, 'addTaxColumns']);
        add_filter('manage_glossary_tag_custom_column', [$this, 'getTaxColumnsValues'], 10, 3);
        add_filter('manage_edit-glossary_tag_sortable_columns', [$this, 'addTaxColumns']);

        add_action('save_post_glossary', [$this, 'savePostMeta']);
    }


    public function makeGlossarySortable($wp_query)
    {
        if (is_admin() && !empty($wp_query->query['post_type'])) {
            $post_type = $wp_query->query['post_type'];
            if ($post_type == 'rrze_glossary') {
                if (!isset($wp_query->query['orderby'])) {
                    $wp_query->set('orderby', 'title');
                    $wp_query->set('order', 'ASC');
                }

                $orderby = $wp_query->get('orderby');
                if ($orderby == 'sortfield') {
                    $wp_query->set('meta_key', 'sortfield');
                    $wp_query->set('orderby', 'meta_value');
                }
            }
        }
    }

    // public function saveSort( $post_id ){
    public function savePostMeta($postID)
    {
        if (!current_user_can('edit_post', $postID) || !isset($_POST['sortfield']) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return $postID;
        }
        update_post_meta($postID, 'source', 'website');
        $lang = substr(get_locale(), 0, 2);
        update_post_meta($postID, 'lang', $lang);
        update_post_meta($postID, 'remoteID', $postID);
        $remoteChanged = get_post_timestamp($postID, 'modified');
        update_post_meta($postID, 'remoteChanged', $remoteChanged);
        update_post_meta($postID, 'sortfield', sanitize_text_field($_POST['sortfield']));
    }

    public function sortboxCallback($meta_id)
    {
        $sortfield = get_post_meta($meta_id->ID, 'sortfield', TRUE);
        $output = '<input type="text" name="sortfield" id="sortfield" class="sortfield" value="' . esc_attr($sortfield) . '">';
        $output .= '<p class="description">' . __('Criterion for sorting the output of the shortcode', 'rrze-glossary') . '</p>';
        echo wp_kses_post($output);
    }


    public function fillContentBox($post)
    {
        $mycontent = apply_filters('the_content', $post->post_content);
        $pos = strpos($mycontent, '<!-- rrze-glossary -->');

        if ($pos !== false) {
            $mycontent = substr($mycontent, 0, $pos);
        }

        echo '<h1>' . esc_html($post->post_title) . '</h1><br>' . wp_kses_post($mycontent);
    }

    public function fillShortcodeBox()
    {
        global $post;
        $ret = '';
        $category = '';
        $tag = '';
        $fields = array('category', 'tag');
        foreach ($fields as $field) {
            $terms = wp_get_post_terms($post->ID, 'rrze_glossary_' . $field);
            foreach ($terms as $term) {
                $$field .= $term->slug . ', ';
            }
            $$field = rtrim($$field, ', ');
        }

        if ($post->ID > 0) {
            $ret .= '<h3 class="hndle">' . __('Single entries', 'rrze-glossary') . ':</h3><p>[glossary id="' . $post->ID . '"]</p>';
            $ret .= ($category ? '<h3 class="hndle">' . __('Accordion with category', 'rrze-glossary') . ':</h3><p>[glossary category="' . $category . '"]</p><p>' . __('If there is more than one category listed, use at least one of them.', 'rrze-glossary') . '</p>' : '');
            $ret .= ($tag ? '<h3 class="hndle">' . __('Accordion with tag', 'rrze-glossary') . ':</h3><p>[glossary tag="' . $tag . '"]</p><p>' . __('If there is more than one tag listed, use at least one of them.', 'rrze-glossary') . '</p>' : '');
            $ret .= '<h3 class="hndle">' . __('Accordion with all entries', 'rrze-glossary') . ':</h3><p>[glossary]</p>';
        }
        echo wp_kses_post($ret);
    }


    public function toggleEditor()
    {
        $post_id = (isset($_GET['post']) ? $_GET['post'] : (isset($_POST['post_ID']) ? $_POST['post_ID'] : 0));
        if ($post_id) {
            if (get_post_type($post_id) == 'rrze_glossary') {
                $source = get_post_meta($post_id, "source", TRUE);
                if ($source) {
                    if ($source != 'website') {
                        $api = new API();
                        $domains = $api->getDomains();
                        $remoteID = get_post_meta($post_id, "remoteID", TRUE);
                        $link = $domains[$source] . 'wp-admin/post.php?post=' . $remoteID . '&action=edit';
                        remove_post_type_support('rrze_glossary', 'title');
                        remove_post_type_support('rrze_glossary', 'editor');
                        remove_meta_box('rrze_glossary_categorydiv', 'rrze_glossary', 'side');
                        remove_meta_box('tagsdiv-rrze_glossary_tag', 'rrze_glossary', 'side');
                        // remove_meta_box( 'submitdiv', 'rrze_glossary', 'side' ); 2020-25-05 : we need submitdiv because of sortbox            
                        add_meta_box(
                            'read_only_content_box', // id, used as the html id att
                            __('This glossary cannot be edited because it is sychronized', 'rrze-glossary') . '. <a href="' . $link . '" target="_blank">' . __('You can edit it at the source', 'rrze-glossary') . '</a>',
                            [$this, 'fillContentBox'], // callback function, spits out the content
                            'rrze_glossary', // post type or page. This adds to posts only
                            'normal', // context, where on the screen
                            'high' // priority, where should this go in the context
                        );
                    }
                }

                if (!use_block_editor_for_post($post_id)) {
                    add_meta_box(
                        'shortcode_box', // id, used as the html id att
                        __('Integration in pages and posts', 'rrze-glossary'), // meta box title
                        [$this, 'fillShortcodeBox'], // callback function, spits out the content
                        'rrze_glossary', // post type or page. This adds to posts only
                        'normal'
                    );
                }
            }
        }
        add_meta_box(
            'sortbox', // id, used as the html id att
            __('Sort', 'rrze-glossary'), // meta box title
            [$this, 'sortboxCallback'], // callback function, spits out the content
            'rrze_glossary', // post type or page. This adds to posts only
            'side'
            // 'high' // priority, where should this go in the context
        );
    }

    public function addGlossaryColumns($columns)
    {
        $columns['sortfield'] = __('Sort criterion', 'rrze-glossary');
        $columns['source'] = __('Source', 'rrze-glossary');
        $columns['id'] = __('ID', 'rrze-glossary');
        return $columns;
    }

    public function addGlossarySortableColumns($columns)
    {
        $columns['taxonomy-rrze_glossary_category'] = __('Category', 'rrze-glossary');
        $columns['taxonomy-rrze_glossary_tag'] = __('Tag', 'rrze-glossary');
        $columns['sortfield'] = 'sortfield';
        $columns['source'] = __('Source', 'rrze-glossary');
        $columns['id'] = __('ID', 'rrze-glossary');
        return $columns;
    }


    public function addGlossaryFilters($post_type)
    {
        if ($post_type !== 'rrze_glossary') {
            return;
        }
        $taxonomies_slugs = array(
            'rrze_glossary_category',
            'rrze_glossary_tag'
        );
        foreach ($taxonomies_slugs as $slug) {
            $taxonomy = get_taxonomy($slug);
            $selected = (isset($_REQUEST[$slug]) ? $_REQUEST[$slug] : '');
            wp_dropdown_categories(array(
                'show_option_all' => $taxonomy->labels->all_items,
                'taxonomy' => $slug,
                'name' => $slug,
                'orderby' => 'name',
                'value_field' => 'slug',
                'selected' => $selected,
                'hierarchical' => TRUE,
                'show_count' => TRUE
            ));
        }
    }

    public function addTaxColumns($columns)
    {
        $columns['source'] = __('Source', 'rrze-glossary');
        return $columns;
    }

    public function getGlossaryColumnsValues($column_name, $post_id)
    {
        if ($column_name == 'id') {
            echo esc_html($post_id);
        }
        if ($column_name == 'source') {
            echo esc_html(get_post_meta($post_id, 'source', true));
        }
        if ($column_name == 'sortfield') {
            echo esc_html(get_post_meta($post_id, 'sortfield', true));
        }
    }

    public function getTaxColumnsValues($content, $column_name, $term_id)
    {
        if ($column_name == 'source') {
            $source = get_term_meta($term_id, 'source', true);
            echo esc_html($source);
        }
    }

    public static function getTermLinks(&$postID, $mytaxonomy)
    {
        $ret = '';
        $terms = wp_get_post_terms($postID, $mytaxonomy);

        foreach ($terms as $term) {
            $ret .= '<a href="' . get_term_link($term->slug, $mytaxonomy) . '">' . $term->name . '</a>, ';
        }
        return substr($ret, 0, -2);
    }


    public static function getThemeGroup()
    {
        $constants = getConstants();
        $ret = '';
        $active_theme = wp_get_theme();
        $active_theme = $active_theme->get('Name');

        if (in_array($active_theme, $constants['fauthemes'])) {
            $ret = 'fauthemes';
        } elseif (in_array($active_theme, $constants['rrzethemes'])) {
            $ret = 'rrzethemes';
        }
        return $ret;
    }

}
