<?php

namespace RRZE\Glossary;

use RRZE\Glossary\API;
use function RRZE\Glossary\Config\logIt;


defined('ABSPATH') || exit;


class Sync {

    public function doSync( $mode ) {
        $tStart = microtime( TRUE );
        date_default_timezone_set('Europe/Berlin');
        $max_exec_time = ini_get('max_execution_time') - 40; // ini_get('max_execution_time') is not the correct value perhaps due to load-balancer or proxy or other fancy things I've no clue of. But this workaround works for now.
        $iCnt = 0;
        $api = new API();
        $domains = $api->getDomains();
        $options = get_option( 'rrze-glossary' );
        $allowSettingsError = ( $mode == 'manual' ? TRUE : FALSE );
        $syncRan = FALSE;
        foreach( $domains as $shortname => $url ){            
            $tStartDetail = microtime( TRUE );
            if ( isset( $options['glossarysync_donotsync_' . $shortname] ) && $options['glossarysync_donotsync_' . $shortname ] != 'on' ){
                $categories = ( isset( $options['glossarysync_categories_' . $shortname] ) ? implode( ',', $options['glossarysync_categories_' . $shortname] ) : FALSE );
                if ( $categories ){
                    $aCnt = $api->setGlossary( $url, $categories, $shortname  );
                    $syncRan = TRUE;
                    foreach( $aCnt['URLhasSlider'] as $URLhasSlider ){
                        $error_msg = __( 'Domain', 'rrze-glossary' ) . ' "' . $shortname . '": ' . __( 'Synchronization error. This glossary contains sliders ([gallery]) and cannot be synchronized:', 'rrze-glossary' ) . ' ' . $URLhasSlider;
                        logIt( $error_msg . ' | ' . $mode );
                        if ( $allowSettingsError ){
                            add_settings_error( 'Synchronization error', 'syncerror', $error_msg, 'error' );
                        }
                    }
                    $sync_msg = __( 'Domain', 'rrze-glossary' ) . ' "' . $shortname . '": ' . __( 'Synchronization completed.', 'rrze-glossary' ) . ' ' . $aCnt['iNew'] . ' ' . __( 'new', 'rrze-glossary' ) . ', ' . $aCnt['iUpdated'] . ' ' . __( ' updated', 'rrze-glossary' ) . ' ' . __( 'and', 'rrze-glossary' ) . ' ' . $aCnt['iDeleted'] . ' ' . __( 'deleted', 'rrze-glossary' ) . '. ' . __('Required time:', 'rrze-glossary') . ' ' . sprintf( '%.1f ', microtime( TRUE ) - $tStartDetail ) . __( 'seconds', 'rrze-glossary' );
                    logIt( $sync_msg . ' | ' . $mode );
                    if ( $allowSettingsError ){
                        add_settings_error( 'Synchronization completed', 'synccompleted', $sync_msg, 'success' );
                    }
                }
            }
        }        

        if ( $syncRan ){
            $sync_msg = __( 'All synchronizations completed', 'rrze-glossary' ) . '. ' . __('Required time:', 'rrze-glossary') . ' ' . sprintf( '%.1f ', microtime( true ) - $tStart ) . __( 'seconds', 'rrze-glossary' );
        } else {
            $sync_msg = __( 'Settings updated', 'rrze-glossary' );
        }
        if ( $allowSettingsError ){
            add_settings_error( 'Synchronization completed', 'synccompleted', $sync_msg, 'success' );
            settings_errors();
        }
        logIt( $sync_msg . ' | ' . $mode );
        return;
    }
}
