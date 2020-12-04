<?php

namespace RRZE\Glossary\Config;

defined('ABSPATH') || exit;

/**
 * Gibt der Name der Option zurück.
 * @return array [description]
 */
function getOptionName() {
    return 'rrze-glossary';
}

function getConstants() {
	$options = array(
		'fauthemes' => [
			'FAU-Einrichtungen',
			'FAU-Einrichtungen-BETA',
			'FAU-Medfak',
			'FAU-RWFak',
			'FAU-Philfak',
			'FAU-Techfak',
			'FAU-Natfak',
			'FAU-Blog',
			'FAU-Jobs'
		],
		'rrzethemes' => [
			'RRZE 2019',
		],
		'langcodes' => [
			"de" => __('German','rrze-synonym'),
			"en" => __('English','rrze-synonym'),
			"es" => __('Spanish','rrze-synonym'),
			"fr" => __('French','rrze-synonym'),
			"ru" => __('Russian','rrze-synonym'),
			"zh" => __('Chinese','rrze-synonym')
		]
	);               
	return $options;
}

/**
 * Gibt die Einstellungen des Menus zurück.
 * @return array [description]
 */
function getMenuSettings() {
    return [
        'page_title'    => __('RRZE Glossary', 'rrze-glossary'),
        'menu_title'    => __('RRZE Glossary', 'rrze-glossary'),
        'capability'    => 'manage_options',
        'menu_slug'     => 'rrze-glossary',
        'title'         => __('RRZE Glossary Settings', 'rrze-glossary'),
    ];
}

/**
 * Gibt die Einstellungen der Inhaltshilfe zurück.
 * @return array [description]
 */
function getHelpTab() {
    return [
        [
            'id'        => 'rrze-glossary-help',
            'content'   => [
                '<p>' . __('Here comes the Context Help content.', 'rrze-glossary') . '</p>'
            ],
            'title'     => __('Overview', 'rrze-glossary'),
            'sidebar'   => sprintf('<p><strong>%1$s:</strong></p><p><a href="https://blogs.fau.de/webworking">RRZE Webworking</a></p><p><a href="https://github.com/RRZE Webteam">%2$s</a></p>', __('For more information', 'rrze-glossary'), __('RRZE Webteam on Github', 'rrze-glossary'))
        ]
    ];
}

/**
 * Gibt die Einstellungen der Optionsbereiche zurück.
 * @return array [description]
 */

function getSections() {
	return [ 
		[
			'id'    => 'glossarydoms',
			'title' => __('Domains', 'rrze-glossary' )
		],
		[
			'id'    => 'glossarysync',
			'title' => __('Synchronize', 'rrze-glossary' )
		],
		[
		  	'id' => 'glossarylog',
		  	'title' => __('Logfile', 'rrze-glossary' )
		]
	];   
}

/**
 * Gibt die Einstellungen der Optionsfelder zurück.
 * @return array [description]
 */

function getFields() {
	return [
		'glossarydoms' => [
			[
				'name' => 'new_name',
				'label' => __('Short name', 'rrze-glossary' ),
				'desc' => __('Enter a short name for this domain.', 'rrze-glossary' ),
				'type' => 'text'
			],
			[
				'name' => 'new_url',
				'label' => __('URL', 'rrze-glossary' ),
				'desc' => __('Enter the domain\'s URL you want to receive glossaries from.', 'rrze-glossary' ),
				'type' => 'text'
			]
		],
		'glossarysync' => [
			[
				'name' => 'shortname',
				'label' => __('Short name', 'rrze-glossary' ),
				'desc' => __('Use this name as attribute \'domain\' in shortcode [glossary]', 'rrze-glossary' ),
				'type' => 'plaintext',
				'default' => ''
			],
			[
				'name' => 'url',
				'label' => __('URL', 'rrze-glossary' ),
				'desc' => '',
				'type' => 'plaintext',
				'default' => ''
			],
			[
				'name' => 'categories',
				'label' => __('Categories', 'rrze-glossary' ),
				'desc' => __('Please select the categories you\'d like to fetch glossaries to.', 'rrze-glossary' ),
				'type' => 'multiselect',
				'options' => []
			],
			[
				'name' => 'donotsync',
				'label' => __('Synchronize', 'rrze-glossary' ),
				'desc' => __('Do not synchronize', 'rrze-glossary' ),
				'type' => 'checkbox',
			],
			[
				'name' => 'hr',
				'label' => '',
				'desc' => '',
				'type' => 'line'
			],
			[
				'name' => 'info',
				'label' => __('Info', 'rrze-glossary' ),
				'desc' => __('All glossaries that match to the selected categories will be updated or inserted. Already synchronized glossaries that refer to categories which are not selected will be deleted. Glossaries that have been deleted at the remote website will be deleted on this website, too.', 'rrze-glossary'),
				'type' => 'plaintext',
				'default' => __('All glossaries that match to the selected categories will be updated or inserted. Already synchronized glossaries that refer to categories which are not selected will be deleted. Glossaries that have been deleted at the remote website will be deleted on this website, too.', 'rrze-glossary'),
			],
			[
				'name' => 'autosync',
				'label' => __('Mode', 'rrze-glossary' ),
				'desc' => __('Synchronize automatically', 'rrze-glossary' ),
				'type' => 'checkbox',
			],
			[
				'name' => 'frequency',
				'label' => __('Frequency', 'rrze-glossary' ),
				'desc' => '',
				'default' => 'daily',
				'options' => [
					'daily' => __('daily', 'rrze-glossary' ),
					'twicedaily' => __('twicedaily', 'rrze-glossary' )
				],
				'type' => 'select'
			],
		],		
    	'glossarylog' => [
        	[
          		'name' => 'glossarylogfile',
          		'type' => 'logfile',
          		'default' => GLOSSARYLOGFILE
        	]
      	]
	];
}


/**
 * Gibt die Einstellungen der Parameter für Shortcode für den klassischen Editor und für Gutenberg zurück.
 * @return array [description]
 */

function getShortcodeSettings(){
	return [
		'block' => [
            'blocktype' => 'rrze-glossary/glossary',
			'blockname' => 'glossary',
			'title' => 'RRZE Glossary',
			'category' => 'widgets',
            'icon' => 'editor-book',
            'show_block' => 'content',
			'message' => __( 'Find the settings on the right side', 'rrze-glossary' )
		],
        'register' => [
			'values' => [
				'' => __( 'none', 'rrze-glossary' ),
				'category' => __( 'Categories', 'rrze-glossary' ),
				'tag' => __( 'Tags', 'rrze-glossary' )
			],
			'default' => '',
			'field_type' => 'select',
			'label' => __( 'Register content', 'rrze-glossary' ),
			'type' => 'string'
		],
        'registerstyle' => [
			'values' => [
				'' => __( '-- hidden --', 'rrze-glossary' ),
				'a-z' => __( 'A - Z', 'rrze-glossary' ),
				'tagcloud' => __( 'Tagcloud', 'rrze-glossary' ),
				'tabs' => __( 'Tabs', 'rrze-glossary' )
			],
			'default' => 'a-z',
			'field_type' => 'select',
			'label' => __( 'Register style', 'rrze-glossary' ),
			'type' => 'string'
		],
		'category' => [
			'default' => '',
			'field_type' => 'text',
			'label' => __( 'Categories', 'rrze-glossary' ),
			'type' => 'text'
        ],
		'tag' => [
			'default' => '',
			'field_type' => 'text',
			'label' => __( 'Tags', 'rrze-glossary' ),
			'type' => 'text'
        ],
		'id' => [
			'default' => NULL,
			'field_type' => 'text',
			'label' => __( 'Glossary', 'rrze-glossary' ),
			'type' => 'number'
		],
		'hide_accordeon' => [
			'field_type' => 'toggle',
			'label' => __( 'Hide accordeon', 'rrze-glossary' ),
			'type' => 'boolean',
			'default' => FALSE,
			'checked'   => FALSE
		],	  
		'hide_title' => [
			'field_type' => 'toggle',
			'label' => __( 'Hide title', 'rrze-glossary' ),
			'type' => 'boolean',
			'default' => FALSE,
			'checked'   => FALSE
		],	  
		'expand_all_link' => [
			'field_type' => 'toggle',
			'label' => __( 'Show "expand all" button', 'rrze-glossary' ),
			'type' => 'boolean',
			'default' => FALSE,
			'checked'   => FALSE
		],	  
		'load_open' => [
			'field_type' => 'toggle',
			'label' => __( 'Load website with opened accordeons', 'rrze-glossary' ),
			'type' => 'boolean',
			'default' => FALSE,
			'checked'   => FALSE
		],	  
		'color' => [
			'values' => [
				'med' => 'med',
				'nat' => 'nat',
				'rw' => 'rw',
				'phil' => 'phil',
				'tk' => 'tk'
			],
			'default' => 'tk',
			'field_type' => 'select',
			'label' => __( 'Color', 'rrze-glossary' ),
			'type' => 'string'
		],
		'additional_class' => [
			'default' => '',
			'field_type' => 'text',
			'label' => __( 'Additonal CSS-class(es) for sourrounding DIV', 'rrze-glossary' ),
			'type' => 'text'
		],
        'sort' => [
			'values' => [
				'title' => __( 'Title', 'rrze-glossary' ),
				'id' => __( 'ID', 'rrze-glossary' ),
				'sortfield' => __( 'Sort field', 'rrze-glossary' )
			],
			'default' => 'title',
			'field_type' => 'select',
			'label' => __( 'Sort', 'rrze-glossary' ),
			'type' => 'string'
		],
        'order' => [
			'values' => [
				'ASC' => __( 'ASC', 'rrze-glossary' ),
				'DESC' => __( 'DESC', 'rrze-glossary' )
			],
			'default' => 'ASC',
			'field_type' => 'select',
			'label' => __( 'Order', 'rrze-glossary' ),
			'type' => 'string'
		]				
    ];
}

function logIt( $msg ){
	date_default_timezone_set('Europe/Berlin');
	$msg = date("Y-m-d H:i:s") . ' | ' . $msg;
	if ( file_exists( GLOSSARYLOGFILE ) ){
		$content = file_get_contents( GLOSSARYLOGFILE );
		$content = $msg . "\n" . $content;
	}else {
		$content = $msg;
	}
	file_put_contents( GLOSSARYLOGFILE, $content, LOCK_EX);
}
  
function deleteLogfile(){
	unlink( GLOSSARYLOGFILE );
}
  

