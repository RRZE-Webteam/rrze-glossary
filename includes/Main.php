<?php

namespace RRZE\Glossary;

defined('ABSPATH') || exit;

use function RRZE\Glossary\Config\logIt;
use function RRZE\Glossary\Config\deleteLogfile;
use RRZE\Glossary\API;
use RRZE\Glossary\CPT;
use RRZE\Glossary\Layout;
use RRZE\Glossary\RESTAPI;
use RRZE\Glossary\Settings;
use RRZE\Glossary\Shortcode;


/**
 * Hauptklasse (Main)
 */
class Main {
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */
    protected $pluginFile;

    protected $settings;

    /**
     * Variablen Werte zuweisen.
     * @param string $pluginFile Pfad- und Dateiname der Plugin-Datei
     */
    public function __construct($pluginFile) {
        $this->pluginFile = $pluginFile;
    }

    /**
     * Es wird ausgeführt, sobald die Klasse instanziiert wird.
     */
    public function onLoaded() {
        add_action( 'wp_enqueue_scripts', [$this, 'enqueueScripts'] );
        // Actions: sync, add domain, delete domain, delete logfile
        add_action( 'update_option_rrze-glossary', [$this, 'checkSync'] );
        add_filter( 'pre_update_option_rrze-glossary',  [$this, 'switchTask'], 10, 1 );

        $cpt = new CPT(); 

        $this->settings = new Settings($this->pluginFile);
        $this->settings->onLoaded();

        $restAPI = new RESTAPI();
        $layout = new Layout();
        $shortcode = new Shortcode();

        // Auto-Sync
        add_action( 'rrze_glossary_auto_sync', [$this, 'runGlossaryCronjob'] );
    }


    /**
     * Enqueue der globale Skripte.
     */
    public function enqueueScripts() {
        wp_register_style('rrze-glossary-styles', plugins_url('assets/css/rrze-glossary.min.css', plugin_basename($this->pluginFile)));
    }


    /**
     * Click on buttons "sync", "add domain", "delete domain" or "delete logfile"
     */
    public function switchTask( $options ) {
        $api = new API();
        $domains = $api->getDomains();

        // get stored options because they are generated and not defined in config.php
        $storedOptions = get_option( 'rrze-glossary' );
        if (is_array($storedOptions)){
            $options = array_merge($storedOptions, $options);
        }
        $tab = ( isset($_GET['glossarydoms'] ) ? 'doms' : ( isset( $_GET['glossarysync'] ) ? 'sync' : ( isset( $_GET['glossarydel'] ) ? 'del' : '' ) ) );

        switch ( $tab ){
            case 'doms':
                if ( $options['glossarydoms_new_name'] && $options['glossarydoms_new_url'] ){
                    // add new domain
                    $aRet = $api->setDomain( $options['glossarydoms_new_name'], $options['glossarydoms_new_url'], $domains );
                    
                    if ( $aRet['status'] ){
                        // url is correct, RRZE-Glossary at given url is in use and shortname is new
                        $domains[$aRet['ret']['cleanShortname']] = $aRet['ret']['cleanUrl'];
                    }else{
                        add_settings_error( 'glossarydoms_new_url', 'glossarydoms_new_error', $aRet['ret'], 'error' );        
                    }
                } else {
                    // delete domain(s)
                    foreach ( $_POST as $key => $url ){
                        if ( substr( $key, 0, 11 ) === "del_domain_" ){
                            if (($shortname = array_search($url, $domains)) !== false) {
                                unset($domains[$shortname]);
                                $api->deleteGlossary( $shortname );
                            }
                            unset($options['glossarysync_categories_' . $shortname]);
                            unset($options['glossarysync_donotsync_' . $shortname]);
                        }
                    }
                }    
            break;
            case 'sync':
                $options['timestamp'] = time();
            break;
            case 'del':
                deleteLogfile();
            break;
        }

        if ( !$domains ){
            // unset this option because $api->getDomains() checks isset(..) because of asort(..)
            unset( $options['registeredDomains'] );
        } else {
            $options['registeredDomains'] = $domains;
        }

        // we don't need these temporary fields to be stored in database table options
        // domains are stored as shortname and url in registeredDomains
        // categories and donotsync are stored in glossarysync_categories_<SHORTNAME> and glossarysync_donotsync_<SHORTNAME>
        unset($options['glossarydoms_new_name']);
        unset($options['glossarydoms_new_url']);
        unset($options['glossarysync_shortname']);
        unset($options['glossarysync_url']);
        unset($options['glossarysync_categories']);
        unset($options['glossarysync_donotsync']);
        unset($options['glossarysync_hr']);

        return $options;
    }


    public function checkSync() {
        if ( isset( $_GET['sync'] ) ){
            $sync = new Sync();
            $sync->doSync( 'manual' );

            $this->setGlossaryCronjob();
        }
    }

    public function runGlossaryCronjob() {
        // sync hourly
        $sync = new Sync();
        $sync->doSync( 'automatic' );
    }

    public function setGlossaryCronjob() {
        date_default_timezone_set( 'Europe/Berlin' );

        $options = get_option( 'rrze-glossary' );

        if ( $options['glossarysync_autosync'] != 'on' ) {
            wp_clear_scheduled_hook( 'rrze_glossary_auto_sync' );
            return;
        }

        $nextcron = 0;
        switch( $options['glossarysync_frequency'] ){
            case 'daily' : $nextcron = 86400;
                break;
            case 'twicedaily' : $nextcron = 43200;
                break;
        }

        $nextcron += time();
        wp_clear_scheduled_hook( 'rrze_glossary_auto_sync' );
        wp_schedule_event( $nextcron, $options['glossarysync_frequency'], 'rrze_glossary_auto_sync' );

        $timestamp = wp_next_scheduled( 'rrze_glossary_auto_sync' );
        $message = __( 'Next automatically synchronization:', 'rrze-glossary' ) . ' ' . date( 'd.m.Y H:i:s', $timestamp );
        add_settings_error( 'AutoSyncComplete', 'autosynccomplete', $message , 'updated' );
        settings_errors();
    }
}
