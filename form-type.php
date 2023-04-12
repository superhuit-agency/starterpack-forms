<?php

namespace SUPT\StarterpackForms\Type;

use function SUPT\StarterpackForms\Helpers\slugify;
use function SUPT\StarterpackForms\Helpers\truncate;

/**
 * Constants
 */
const NAME = 'form';
const COLUMN_FIELDS_NAME = 'fields';
const COLUMN_NOTIFICATION_NAME = 'notification';

const META_KEY_FIELDS_NAME = 'form_fields';

// Allowed block in the block editor
const ALLOWED_BLOCKS = [
	'supt/checkbox',
	'supt/form-section-breaker',
	'supt/input-checkbox',
	'supt/input-email',
	'supt/input-file',
	'supt/input-option-radio',
	'supt/input-radio',
	'supt/input-select',
	'supt/input-text',
	'supt/input-textarea',
	'supt/radio',
];

/**
 * Action & filter hooks
 */
add_action( 'init', __NAMESPACE__.'\register' );
add_action( 'init', __NAMESPACE__.'\register_options_page' );

add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\enqueue_admin_assets' );
add_filter( 'allowed_block_types_all', __NAMESPACE__.'\allowed_block_types', 10, 2);

add_action( 'rest_after_insert_'. NAME, __NAMESPACE__.'\save_fields_config' );

add_action( 'request', __NAMESPACE__.'\order_by_title' );
add_filter( 'manage_edit-'. NAME .'_columns', __NAMESPACE__.'\register_columns_fields' );
add_action( 'manage_'. NAME .'_posts_custom_column', __NAMESPACE__.'\populate_columns_fields', 10, 2 );

add_filter( 'acf/settings/load_json', __NAMESPACE__.'\load_acf_json' );

add_filter( 'acf/prepare_field/key=field_5cfa32d80079f', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // form_email_to
add_filter( 'acf/prepare_field/key=field_5cfa3309007a0', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // form_email_from
add_filter( 'acf/prepare_field/key=field_5cfa3322007a1', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // form_name_from
add_filter( 'acf/prepare_field/key=field_5cfa2db2e2fbe', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // message_success
add_filter( 'acf/prepare_field/key=field_5cfa2dc6e2fbf', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // message_error
add_filter( 'acf/prepare_field/key=field_5cfa2f0ada5e2', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // email_autoreply - subject
add_filter( 'acf/prepare_field/key=field_5cfa2f0ada5e3', __NAMESPACE__.'\acf_prepare_field_placeholder' ); // email_autoreply - body
add_action( 'acf/render_field/key=field_5cfa2f0ada5e3' , __NAMESPACE__.'\acf_render_wysiwyg_placeholder' ); // email_autoreply - body
add_filter( 'acf/prepare_field/key=field_5cfa2f0ada5e3', __NAMESPACE__.'\acf_prepare_field_email_body_desc' );

add_action( 'rest_api_init', __NAMESPACE__.'\rest_api_register_metas' );

add_filter( 'spcki18n_translation_strings_filepaths', __NAMESPACE__.'\add_translation_strings_filepath' );


function register() {
	register_post_type(NAME,
		array(
			'labels' => array(
				'name'                  => _x( 'Forms', 'Post Type General Name', 'spckforms' ),
				'singular_name'         => _x( 'Form', 'Post Type Singular Name', 'spckforms' ),
				'menu_name'             => __( 'Forms', 'spckforms' ),
				'name_admin_bar'        => __( 'Form', 'spckforms' ),
				'archives'              => __( 'Form Archives', 'spckforms' ),
				'attributes'            => __( 'Form Attributes', 'spckforms' ),
				'parent_item_colon'     => __( '', 'spckforms' ),
				'all_items'             => __( 'All Forms', 'spckforms' ),
				'add_new_item'          => __( 'Add New Form', 'spckforms' ),
				'add_new'               => __( 'Add New', 'spckforms' ),
				'new_item'              => __( 'New Form', 'spckforms' ),
				'edit_item'             => __( 'Edit Form', 'spckforms' ),
				'update_item'           => __( 'Update Form', 'spckforms' ),
				'view_item'             => __( 'View Form', 'spckforms' ),
				'view_items'            => __( 'View Forms', 'spckforms' ),
				'search_items'          => __( 'Search Form', 'spckforms' ),
				'not_found'             => __( 'Not found', 'spckforms' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'spckforms' ),
				'featured_image'        => __( 'Featured Image', 'spckforms' ),
				'set_featured_image'    => __( 'Set featured image', 'spckforms' ),
				'remove_featured_image' => __( 'Remove featured image', 'spckforms' ),
				'use_featured_image'    => __( 'Use as featured image', 'spckforms' ),
				'insert_into_item'      => __( 'Insert into Form', 'spckforms' ),
				'uploaded_to_this_item' => __( 'Uploaded to this Form', 'spckforms' ),
				'items_list'            => __( 'Form list', 'spckforms' ),
				'items_list_navigation' => __( 'Form list navigation', 'spckforms' ),
				'filter_items_list'     => __( 'Filter Form list', 'spckforms' ),
			),
			'label'                 => __( 'Form', 'spckforms' ),
			'menu_icon'             => 'dashicons-feedback',
			'supports'              => array(
				'title',
				'editor',
				'revisions',
				'custom-fields',
			),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
			'map_meta_cap'          => true,
			'show_in_rest'          => true,
			'rewrite' => false, //array('slug' => 'events', 'with_front' => false),
		)
	);
}

function enqueue_admin_assets() {
	if ( get_post_type() !== NAME ) return;

	wp_enqueue_style( 'form-edit-table', SPCKFORMS_URI . 'assets/form-edit-table.css', false, SPCKFORMS_PLUGIN_VERSION );
	wp_enqueue_style( 'admin-wysiwyg-placeholder', SPCKFORMS_URI . 'assets/admin-wysiwyg-placeholder.css', false, SPCKFORMS_PLUGIN_VERSION );
}

function allowed_block_types( $allowed_block_types, $block_editor_context ) {
	return ( $block_editor_context->post && $block_editor_context->post->post_type === NAME )
		? apply_filters( 'spckforms_allowed_block_types', ALLOWED_BLOCKS )
		: $allowed_block_types;
}

function register_options_page() {

	if( ! function_exists('acf_add_options_page') ) return;

	acf_add_options_sub_page(array(
		'page_title' 	=> __('Forms settings', 'spckforms'),
		'menu_title'	=> __( 'Settings' ), // uses WordPress text domain so it's already translated
		'menu_slug' 	=> 'form-general-settings',
		'parent_slug'	=> 'edit.php?post_type='. NAME,
		'capability'	=> 'edit_pages',
	));
}

/**
 * Saves the fields configuration as a meta field
 * in order to be able to validate + sanitize on form submission.
 *
 * @param WP_Post $post Inserted or updated post object.
 */
function save_fields_config( $post ) {
	$blocks = parse_blocks( $post->post_content );

	/**
	 * Filters The allowed block types in the Form CPT editor.
	 *
	 * @since 0.0.1
	 *
	 * @param Array[string] Block types name
	 */
	$allowed_block_types = apply_filters( 'spckforms_allowed_block_types', ALLOWED_BLOCKS );

	$fields = array_reduce($blocks,function($carry, $block) use ($allowed_block_types) {
		if ( in_array( $block['blockName'], $allowed_block_types ) ) {

			$attributes = $block['attrs'];
			if ( empty($attributes['label']) ) $attributes['label'] = ( isset($attributes['legend']) ? $attributes['legend'] : '' );
			if ( empty($attributes['name']) ) $attributes['name'] = slugify($attributes['label'], '_');

			$carry[] = [
				'block' => $block['blockName'],
				'attributes' => $attributes,
				'children'	 => $block['innerBlocks'],
			];
		}

		return $carry;
	});

	update_post_meta( $post->ID, META_KEY_FIELDS_NAME, $fields );
}

/**
 * Set the default order to "Title ASC" if no order set
 *
 * @param array $query_vars The array of requested query variables.
 */
function order_by_title( $query_vars ) {
	global $pagenow, $post_type;
	if ( !is_admin() || $pagenow != 'edit.php' || $post_type != NAME ) {
		return $query_vars;
	}

	if ( empty(filter_input(INPUT_GET, 'orderby')) && empty(filter_input(INPUT_GET, 'order')) ) {
		$query_vars['orderby'] = 'title';
		$query_vars['order'] = 'ASC';
	}

	return $query_vars;
}

/**
 * Add extra columns in edit table
 *
 * @param array $post_columns An array of column names.
 */
function register_columns_fields( $columns ) {
	return array_merge(
		array_slice( $columns, 0, 2 ),
		array( COLUMN_NOTIFICATION_NAME => __('Notification email', 'spckforms') ),
		array( COLUMN_FIELDS_NAME => __('Fields', 'spckforms') ),
		array_slice( $columns, 2 )
	);
}

/**
 * Populate extra columns with specific content, if available
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id     The current post ID.
 */
function populate_columns_fields( $column_name, $post_id ) {
	$values = [];
	switch ($column_name) {
		case COLUMN_FIELDS_NAME:
			$fields = get_post_meta( $post_id, META_KEY_FIELDS_NAME, true );
			if ( !is_array($fields) ) $fields = [];

			// Remove unneeded fields
			$filtered_fields = array_reduce($fields, function($acc, $item) {
				if($item['block'] !== 'supt/form-section-breaker') {
					$acc[] = $item;
				}
				return $acc;
			}, []);

			$values[] = implode(', ', array_map( function($f) {
				return truncate($f['attributes']['label']) . (isset($f['attributes']['required']) ? '*' : '');
			}, $filtered_fields ) );
			break;

		case COLUMN_NOTIFICATION_NAME:
			if ( function_exists('get_field') ) {
				$notif_email = get_field( 'form_email_to', $post_id );
				$values[] = ( empty($notif_email) ? get_field( 'form_email_to', 'options' ) : $notif_email );
			}
			break;
	}

	echo implode('<br>', $values);
}

/**
 * Include load folder for ACF fields in json
 */
function load_acf_json($paths) {
	$paths[] = SPCKFORMS_PATH.'/acf-json';
	return $paths;
}

/**
 * Populate the placholder attribute with the globale Options
 */
function acf_prepare_field_placeholder( $field ) {
	if ( $field['prefix'] === 'acf' ) {
		$field['placeholder'] = get_field( $field['_name'], 'options' );
	}
	else {
		$parent = get_field_object($field['parent']);
		$field_parent = get_field( $parent['_name'], 'options' );
		if ( $field_parent !== null ) {
			$field['placeholder'] = $field_parent[ $field['_name'] ];
		}
	}

	return $field;
}

/**
 * Display a placeholder to WYSIWYG editor
 * when it has a delayed initialisation
 */
function acf_render_wysiwyg_placeholder( $field ) {
	if ($field['delay'] == 1) {
		$value = $field['value'];
		if ( empty($value) ) {
			$value = $field['default_value'];

			if ( empty($value) && isset($field['placeholder'])) {
				$value = $field['placeholder'];
			}
		}

		if ( !empty($value) ) {
			echo '<div class="acf-wysiwyg-placeholder">'. $value .'</div>';
		}
	}

	return $field;
}

function acf_prepare_field_email_body_desc( $field ) {
	$fields = get_post_meta( get_the_ID(), META_KEY_FIELDS_NAME, true );

	if (!is_array($fields) ) $fields = [];
	$fields_variables = array_map(
		function($f) {
			return "{{&nbsp;{$f['attributes']['name']}&nbsp;}}";
		},
		array_filter(
			$fields,
			function($f) {
				return ( isset($f['attributes']) && isset($f['attributes']['name']) );
			}
		)
	);
	$field['instructions'] .= '<br>'.implode('<br>', $fields_variables);

	return $field;
}

function rest_api_register_metas() {
	register_rest_field( NAME, 'fields', [
		'get_callback' => __NAMESPACE__.'\get_form_fields',
		'schema' => null,
	] );
	register_rest_field( NAME, 'strings', [
		'get_callback' => __NAMESPACE__.'\get_form_strings',
		'schema' => null,
	] );
}

function get_form_fields( $post, $field_name = null, $request = null ) {
	return get_post_meta( $post['id'], META_KEY_FIELDS_NAME, true );
}

function get_form_strings( $post, $field_name = null, $request = null ) {
	return [
		'submitLabel' => get_field( 'submit', $post['id'] ),
	];
}

function add_translation_strings_filepath( $filepaths ) {
	$filepaths[] = SPCKFORMS_PATH .'/translation-strings.json';
	return $filepaths;
}




