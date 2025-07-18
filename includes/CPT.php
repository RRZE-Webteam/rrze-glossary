<?php

namespace RRZE\Glossary;

defined('ABSPATH') || exit;

/**
 * Custom Post Type "glossary"
 */
class CPT
{

    private $lang = '';

    public function __construct()
    {
        $this->lang = substr(get_locale(), 0, 2);
        add_action('init', [$this, 'registerGlossary'], 0);
        add_action('init', [$this, 'registerGlossaryTaxonomy'], 0);
        add_action('publish_glossary', [$this, 'setPostMeta'], 10, 1);
        add_action('create_glossary_category', [$this, 'setTermMeta'], 10, 1);
        add_action('create_glossary_tag', [$this, 'setTermMeta'], 10, 1);
        add_filter('single_template', [$this, 'filter_single_template']);
        add_filter('archive_template', [$this, 'filter_archive_template']);
        add_filter('taxonomy_template', [$this, 'filter_taxonomy_template']);

        add_action('wp_loaded', [$this, 'fixWrongTaxonomies']);
    }


    public function registerGlossary()
    {
        $labels = array(
            'name' => _x('Glossary', 'Glossary entries', 'rrze-glossary'),
            'singular_name' => _x('Glossary', 'Single glossary ', 'rrze-glossary'),
            'menu_name' => __('Glossary', 'rrze-glossary'),
            'add_new' => __('Add glossary', 'rrze-glossary'),
            'add_new_item' => __('Add new glossary', 'rrze-glossary'),
            'edit_item' => __('Edit glossary', 'rrze-glossary'),
            'all_items' => __('All glossaries', 'rrze-glossary'),
            'search_items' => __('Search glossary', 'rrze-glossary'),
        );
        $rewrite = array(
            'slug' => 'glossary',
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        );
        $args = array(
            'label' => __('Glossary', 'rrze-glossary'),
            'description' => __('Glossary informations', 'rrze-glossary'),
            'labels' => $labels,
            'supports' => array('title', 'editor'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_icon' => 'dashicons-book-alt',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => true,
            'publicly_queryable' => true,
            'query_var' => 'glossary',
            'rewrite' => $rewrite,
            'show_in_rest' => true,
            'rest_base' => 'glossary',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
        );
        register_post_type('glossary', $args);
    }

    public function registerGlossaryTaxonomy()
    {
        $tax = [
            [
                'name' => 'glossary_category',
                'label' => __('Glossary', 'rrze-glossary') . ' ' . __('Categories', 'rrze-glossary'),
                'slug' => 'glossary_category',
                'rest_base' => 'glossary_category',
                'hierarchical' => TRUE,
                'labels' => array(
                    'singular_name' => __('Category', 'rrze-glossary'),
                    'add_new' => __('Add new category', 'rrze-glossary'),
                    'add_new_item' => __('Add new category', 'rrze-glossary'),
                    'new_item' => __('New category', 'rrze-glossary'),
                    'view_item' => __('Show category', 'rrze-glossary'),
                    'view_items' => __('Show categories', 'rrze-glossary'),
                    'search_items' => __('Search categories', 'rrze-glossary'),
                    'not_found' => __('No category found', 'rrze-glossary'),
                    'all_items' => __('All categories', 'rrze-glossary'),
                    'separate_items_with_commas' => __('Separate categories with commas', 'rrze-glossary'),
                    'choose_from_most_used' => __('Choose from the most used categories', 'rrze-glossary'),
                    'edit_item' => __('Edit category', 'rrze-glossary'),
                    'update_item' => __('Update category', 'rrze-glossary')
                )
            ],
            [
                'name' => 'glossary_tag',
                'label' => __('Glossary', 'rrze-glossary') . ' ' . __('Tags', 'rrze-glossary'),
                'slug' => 'glossary_tag',
                'rest_base' => 'glossary_tag',
                'hierarchical' => FALSE,
                'labels' => array(
                    'singular_name' => __('Tag', 'rrze-glossary'),
                    'add_new' => __('Add new tag', 'rrze-glossary'),
                    'add_new_item' => __('Add new tag', 'rrze-glossary'),
                    'new_item' => __('New tag', 'rrze-glossary'),
                    'view_item' => __('Show tag', 'rrze-glossary'),
                    'view_items' => __('Show tags', 'rrze-glossary'),
                    'search_items' => __('Search tags', 'rrze-glossary'),
                    'not_found' => __('No tag found', 'rrze-glossary'),
                    'all_items' => __('All tags', 'rrze-glossary'),
                    'separate_items_with_commas' => __('Separate tags with commas', 'rrze-glossary'),
                    'choose_from_most_used' => __('Choose from the most used tags', 'rrze-glossary'),
                    'edit_item' => __('Edit tag', 'rrze-glossary'),
                    'update_item' => __('Update tag', 'rrze-glossary')
                )
            ],
        ];

        foreach ($tax as $t) {
            $ret = register_taxonomy(
                $t['name'],  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
                'glossary',   		 //post type name
                array(
                    'hierarchical' => $t['hierarchical'],
                    'label' => $t['label'], //Display name
                    'labels' => $t['labels'],
                    'show_ui' => TRUE,
                    'show_admin_column' => TRUE,
                    'query_var' => TRUE,
                    'rewrite' => array(
                        'slug' => $t['slug'], // This controls the base slug that will display before each term
                        'with_front' => TRUE // Don't display the category base before
                    ),
                    'show_in_rest' => TRUE,
                    'rest_base' => $t['rest_base'],
                    'rest_controller_class' => 'WP_REST_Terms_Controller'
                )
            );
            register_term_meta(
                $t['name'],
                'source',
                array(
                    'query_var' => TRUE,
                    'type' => 'string',
                    'single' => TRUE,
                    'show_in_rest' => TRUE,
                    'rest_base' => 'source',
                    'rest_controller_class' => 'WP_REST_Terms_Controller'
                )
            );
            register_term_meta(
                $t['name'],
                'lang',
                array(
                    'query_var' => TRUE,
                    'type' => 'string',
                    'single' => TRUE,
                    'show_in_rest' => TRUE,
                    'rest_base' => 'lang',
                    'rest_controller_class' => 'WP_REST_Terms_Controller'
                )
            );
        }
    }

    public function setPostMeta($postID)
    {
        add_post_meta($postID, 'source', 'website', TRUE);
        add_post_meta($postID, 'lang', $this->lang, TRUE);
        add_post_meta($postID, 'remoteID', $postID, TRUE);
        $remoteChanged = get_post_timestamp($postID, 'modified');
        add_post_meta($postID, 'remoteChanged', $remoteChanged, TRUE);
    }

    public function setTermMeta($termID)
    {
        add_term_meta($termID, 'source', 'website', TRUE);
        add_term_meta($termID, 'lang', $this->lang, TRUE);
    }


    public function filter_single_template($template)
    {
        global $post;
        if ('glossary' === $post->post_type) {
            $template = plugin_dir_path(__DIR__) . 'templates/single-glossary.php';
        }
        return $template;
    }

    public function filter_archive_template($template)
    {
        if (is_post_type_archive('glossary')) {
            $template = plugin_dir_path(__DIR__) . 'templates/archive-glossary.php';
        }
        return $template;
    }

    public function filter_taxonomy_template($template)
    {
        if (is_tax('glossary_category')) {
            $template = plugin_dir_path(__DIR__) . 'templates/glossary_category.php';
        } elseif (is_tax('glossary_tag')) {
            $template = plugin_dir_path(__DIR__) . 'templates/glossary_tag.php';
        }
        return $template;
    }


    public function fixWrongTaxonomies()
    {
        global $wpdb;

        if (get_option('rrze_glossary_tax_fix_done')) {
            return;
        }

        $post_ids = $wpdb->get_col("
        SELECT DISTINCT tr.object_id
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
        WHERE p.post_type = 'glossary'
        AND (tt.taxonomy = 'category' OR tt.taxonomy = 'post_tag')
    ");

        foreach ($post_ids as $post_id) {
            $categories = get_the_terms($post_id, 'category');
            if (!empty($categories) && !is_wp_error($categories)) {
                foreach ($categories as $term) {
                    $new_term = term_exists($term->slug, 'glossary_category');
                    if (!$new_term) {
                        $new_term = wp_insert_term($term->name, 'glossary_category', ['slug' => $term->slug]);
                    }
                    wp_set_object_terms($post_id, intval($new_term['term_id']), 'glossary_category', true);
                }
                wp_remove_object_terms($post_id, wp_list_pluck($categories, 'term_id'), 'category');
            }

            $tags = get_the_terms($post_id, 'post_tag');
            if (!empty($tags) && !is_wp_error($tags)) {
                foreach ($tags as $term) {
                    $new_term = term_exists($term->slug, 'glossary_tag');
                    if (!$new_term) {
                        $new_term = wp_insert_term($term->name, 'glossary_tag', ['slug' => $term->slug]);
                    }
                    wp_set_object_terms($post_id, intval($new_term['term_id']), 'glossary_tag', true);
                }
                wp_remove_object_terms($post_id, wp_list_pluck($tags, 'term_id'), 'post_tag');
            }
        }

        update_option('rrze_glossary_tax_fix_done', 1);
    }
}