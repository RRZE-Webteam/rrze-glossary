<?php

namespace RRZE\Glossary;

defined('ABSPATH') || exit;

/**
 * REST API for "glossary"
 */
class RESTAPI {

    public function __construct() {
        add_action('rest_api_init', [$this, 'createPostMeta']);
        add_action('rest_api_init', [$this, 'createTaxDetails']);
        add_action('rest_api_init', [$this, 'createChildren']);
        add_action('rest_api_init', [$this, 'addFilters']);
        add_action('rest_insert_rrze_glossary_category', [$this, 'setParentOnInsert'], 10, 3);
    }

    // -------------------------
    // Post meta for REST
    // -------------------------
    public function getPostSource($object) {
        return get_post_meta($object['id'], 'source', true);
    }

    public function getPostLang($object) {
        return get_post_meta($object['id'], 'lang', true);
    }

    public function getPostRemoteID($object) {
        return get_post_meta($object['id'], 'remoteID', true);
    }

    public function getPostRemoteChanged($object) {
        return get_post_meta($object['id'], 'remoteChanged', true);
    }

    public function createPostMeta() {
        $fields = ['source', 'lang', 'remoteID', 'remoteChanged'];
        $callbacks = [
            'source' => [$this, 'getPostSource'],
            'lang' => [$this, 'getPostLang'],
            'remoteID' => [$this, 'getPostRemoteID'],
            'remoteChanged' => [$this, 'getPostRemoteChanged']
        ];

        foreach ($fields as $field) {
            register_rest_field('rrze_glossary', $field, [
                'get_callback' => $callbacks[$field],
                'schema' => null
            ]);
        }
    }

    // -------------------------
    // Add filter parameters for REST queries
    // -------------------------
    public function addFilterParam($args, $request) {
        if (empty($request['filter']) || !is_array($request['filter'])) {
            return $args;
        }
        global $wp;
        $filter = $request['filter'];
        $vars = apply_filters('query_vars', $wp->public_query_vars);
        foreach ($vars as $var) {
            if (isset($filter[$var])) {
                $args[$var] = $filter[$var];
            }
        }
        return $args;
    }

    public function addFilters() {
        add_filter('rest_glossary_query', [$this, 'addFilterParam'], 10, 2);
        add_filter('rest_glossary_category_query', [$this, 'addFilterParam'], 10, 2);
        add_filter('rest_glossary_tag_query', [$this, 'addFilterParam'], 10, 2);
    }

    // -------------------------
    // Taxonomy fields
    // -------------------------
    public function getGlossaryCategories($post) {
        return wp_get_post_terms($post['id'], 'rrze_glossary_category', ['fields' => 'ids']);
    }

    public function getGlossaryTags($post) {
        return wp_get_post_terms($post['id'], 'rrze_glossary_tag', ['fields' => 'ids']);
    }

    public function getTaxSource($object) {
        return get_term_meta($object['id'], 'source', true);
    }

    public function getTaxLang($object) {
        return get_term_meta($object['id'], 'lang', true);
    }

    public function createTaxDetails() {
        // Register post fields for taxonomy terms
        register_rest_field('rrze_glossary', 'rrze_glossary_category', [
            'get_callback' => [$this, 'getGlossaryCategories'],
            'update_callback' => null,
            'schema' => null
        ]);
        register_rest_field('rrze_glossary', 'rrze_glossary_tag', [
            'get_callback' => [$this, 'getGlossaryTags'],
            'update_callback' => null,
            'schema' => null
        ]);

        // Register term meta for taxonomy
        $taxFields = ['source' => 'getTaxSource', 'lang' => 'getTaxLang'];
        foreach ($taxFields as $field => $callback) {
            register_rest_field('rrze_glossary_category', $field, [
                'get_callback' => [$this, $callback],
                'update_callback' => null,
                'schema' => null
            ]);
            register_rest_field('rrze_glossary_tag', $field, [
                'get_callback' => [$this, $callback],
                'update_callback' => null,
                'schema' => null
            ]);
        }
    }

    // -------------------------
    // Children taxonomy field
    // -------------------------
    public function getChildrenCategories($term) {
        $children = get_terms([
            'taxonomy' => 'rrze_glossary_category',
            'parent' => $term['id'],
            'hide_empty' => false
        ]);

        $ret = [];
        foreach ($children as $child) {
            $ret[] = $child->name;
        }
        return $ret;
    }

    public function createChildren() {
        register_rest_field('rrze_glossary_category', 
        'children', [
            'get_callback' => [$this, 'getChildrenCategories'],
            'update_callback' => null,
            'schema' => null
        ]);
    }

    // -------------------------
    // Set parent term when creating via REST
    // -------------------------
    public function setParentOnInsert($term, $request, $creating) {
        if ($creating && isset($request['parent']) && !empty($request['parent'])) {
            wp_update_term($term->term_id, 'rrze_glossary_category', [
                'parent' => (int) $request['parent']
            ]);
        }
    }
}
