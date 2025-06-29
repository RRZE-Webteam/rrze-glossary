<?php

/*
Plugin Name:     RRZE Glossary
Plugin URI:      https://gitlab.rrze.fau.de/rrze-webteam/rrze-glossary
Description:     Plugin, um Glossar-Einträge zu erstellen und aus dem FAU-Netzwerk zu synchronisieren. Verwendbar als Shortcode, Block oder Widget.
Version:         2.1.4
Requires at least: 6.1
Requires PHP:      8.0
Author:          RRZE Webteam
Author URI:      https://blogs.fau.de/webworking/
License:         GNU General Public License v3
License URI:     http://www.gnu.org/licenses/gpl-3.0.html
Domain Path:     /languages
Text Domain:     rrze-glossary
*/

namespace RRZE\Glossary;

    
defined('ABSPATH') || exit;

use RRZE\Glossary\Main;

$s = array(
    '/^((http|https):\/\/)?(www.)+/i',
    '/\//',
    '/[^A-Za-z0-9\-]/'
);
$r = array(
    '',
    '-',
    '-'
);

define( 'GLOSSARYLOGFILE', plugin_dir_path( __FILE__) . 'rrze-glossary-' . preg_replace( $s, $r,  get_bloginfo( 'url' ) ) . '.log' );


const RRZE_PHP_VERSION = '8.0';
const RRZE_WP_VERSION = '6.1';
const RRZE_PLUGIN_FILE = __FILE__;
const RRZE_SCHEMA_START = '<div style="display:none" itemscope itemtype="https://schema.org/DefinedTerm">';
const RRZE_SCHEMA_END = '</div>';
const RRZE_SCHEMA_TITLE_START = '<div style="display:none" itemscope itemprop="name" itemtype="https://schema.org/name"><div style="display:none" itemprop="name">';
const RRZE_SCHEMA_TITLE_END = '</div>';
const RRZE_SCHEMA_CONTENT_START = '<div style="display:none" itemscope itemprop="description" itemtype="https://schema.org/description"><div style="display:none" itemprop="text">';
const RRZE_SCHEMA_CONTENT_END = '</div></div></div>';



// Automatische Laden von Klassen.
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});



// Registriert die Plugin-Funktion, die bei Aktivierung des Plugins ausgeführt werden soll.
register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
// Registriert die Plugin-Funktion, die ausgeführt werden soll, wenn das Plugin deaktiviert wird.
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');
// Wird aufgerufen, sobald alle aktivierten Plugins geladen wurden.
add_action('plugins_loaded', __NAMESPACE__ . '\loaded');


/**
 * Überprüft die minimal erforderliche PHP- u. WP-Version.
 */
function system_requirements() {
    $error = '';
    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        /* translators: 1: current PHP version, 2: required PHP version */
        $error = sprintf(__('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-glossary'), PHP_VERSION, RRZE_PHP_VERSION);
    } elseif (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        /* translators: 1: current WordPress version, 2: required WordPress version */
        $error = sprintf(__('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-glossary'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }
    return $error;
}

function addMetadata(){
    if (post_type_exists('glossary')){
        // add metadata for posts with CPT glossary
        $postIds = get_posts([
            'post_type' => 'glossary', 
            'nopaging' => true, 
            'fields' => 'ids'
            ]);

        $lang = substr( get_locale(), 0, 2);

        foreach( $postIds as $postID ){
            if (metadata_exists('post', $postID, 'source') === false){
                update_post_meta($postID, 'source', 'website');        
                update_post_meta($postID, 'remoteID', $postID);
                update_post_meta($postID, 'lang', $lang);
                $remoteChanged = get_post_timestamp( $postID, 'modified' );
                update_post_meta( $postID, 'remoteChanged', $remoteChanged );
            }
        }    

        if (taxonomy_exists('glossary_category')){
            // add metadata for glossary_category including their children
            $terms = get_terms([
                'taxonomy' => 'glossary_category',
                'hide_empty' => true
                ]);

            foreach( $terms as $term ){
                if (metadata_exists('term', $term->term_id, 'source') === false){
                    update_term_meta($term->term_id, 'source', 'website');        
                    update_term_meta($term->term_id, 'lang', $lang);        
                }
            }    
        }
    }
}

/**
 * Wird durchgeführt, nachdem das Plugin aktiviert wurde.
 */
function activation() {

    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
    if ($error = system_requirements()) {
        deactivate_plugins(plugin_basename(__FILE__), false, true);
        wp_die(esc_html($error));
    }

    // Ab hier können die Funktionen hinzugefügt werden,
    // die bei der Aktivierung des Plugins aufgerufen werden müssen.
    // Bspw. wp_schedule_event, flush_rewrite_rules, etc.
    // Einmaliger Aufruf: vom Theme gespeicherte Daten zum CPT "glossary" und zur taxonomy "glossary_category" um Metadaten ergaenzen, damit rrze-glossary funktioniert:
    if ( get_option( 'rrze-glossary-metadata' ) != 'added' ) {
        addMetadata();
        update_option( 'rrze-glossary-metadata', 'added' );
    }
}

/**
 * Wird durchgeführt, nachdem das Plugin deaktiviert wurde.
 */
function deactivation() {
    // Hier können die Funktionen hinzugefügt werden, die
    // bei der Deaktivierung des Plugins aufgerufen werden müssen.
    // Bspw. delete_option, wp_clear_scheduled_hook, flush_rewrite_rules, etc.

    // delete_option(Options::get_option_name());
    wp_clear_scheduled_hook( 'rrze_glossary_auto_sync' );
    flush_rewrite_rules();
}

function rrze_glossary_init() {
	register_block_type( __DIR__ . '/build' );
    $script_handle = generate_block_asset_handle( 'create-block/rrze-glossary', 'editorScript' );
    wp_set_script_translations( $script_handle, 'rrze-glossary', plugin_dir_path( __FILE__ ) . 'languages' );
}

/**
 * Wird durchgeführt, nachdem das WP-Grundsystem hochgefahren
 * und alle Plugins eingebunden wurden.
 */
function loaded() {
    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    add_action('init', fn() => load_plugin_textdomain('rrze-glossary', false, dirname(plugin_basename(__FILE__)) . '/languages'));
    
    require_once 'config/config.php';
    
    if ($error = system_requirements()) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_data = get_plugin_data(__FILE__);
        $plugin_name = $plugin_data['Name'];
        $tag = is_network_admin() ? 'network_admin_notices' : 'admin_notices';
        add_action($tag, function () use ($plugin_name, $error) {
            printf('<div class="notice notice-error"><p>%1$s: %2$s</p></div>', esc_html($plugin_name), esc_html($error));
        });
    } else {
        // Hauptklasse (Main) wird instanziiert.
        $main = new Main(__FILE__);
        $main->onLoaded();
    }

    add_action( 'init', __NAMESPACE__ . '\rrze_glossary_init' );

}
