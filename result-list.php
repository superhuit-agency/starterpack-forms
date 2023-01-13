<?php

namespace SUPT\StarterpackForms\ResultList;

use DateTime;
use DatePeriod;
use DateInterval;

use function SUPT\StarterpackForms\Helpers\get_post_meta_db;
use function SUPT\StarterpackForms\Helpers\get_template_part as spckforms_get_template_part;

use const SUPT\StarterpackForms\Type\META_KEY_FIELDS_NAME;
use const SUPT\StarterpackForms\Type\NAME as FORM_TYPE_NAME;

$results_until = null;

/**
 * Constants
 */
const COLUMN_RESULTS_NAME = 'results';

const META_KEY_FORM_SUBMISSION = 'form_result';

const SETTING_SAVE_TO_DB = 'save_to_db';
const SETTING_SAVE_TO_DB_N_SEND_NOTIF = 'save_db_n_send_notification';


/**
 * Action & filter hooks
 */
add_action( 'init', __NAMESPACE__.'\export_results' );
add_action( 'init', __NAMESPACE__.'\delete_results' );
add_action( 'init', __NAMESPACE__.'\trash_result' );

add_action( 'admin_menu', __NAMESPACE__.'\register_results_page' );
add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\enqueue_admin_assets' );

add_filter( 'manage_edit-'. FORM_TYPE_NAME .'_columns', __NAMESPACE__.'\register_columns_fields' );
add_action( 'manage_'. FORM_TYPE_NAME .'_posts_custom_column', __NAMESPACE__.'\populate_columns_fields', 10, 2 );


/**
 * Actions
 */
function export_results() {

	if ( ! (isset($_REQUEST['export-results']) && isset($_REQUEST['form'])) ) return;

	$sents = array_reverse( get_post_meta( $_REQUEST['form'], META_KEY_FORM_SUBMISSION ) );
	if ( empty($sents) ) return;

	$fields = (array)get_field( META_KEY_FIELDS_NAME, $_REQUEST['form'] );
	if ( empty($fields) || !is_array($fields) ) return;

	$header = [];
	$header['timestamp'] = 'Date';
	foreach ($fields as $field) {
		$header[$field['attributes']['name']] = (isset($field['attributes']['legend']) ? $field['attributes']['legend'] : $field['attributes']['label']);
	}

	$form_title = sanitize_title(get_the_title($_REQUEST['form']));
	header('Content-Disposition: attachment; filename="export-results-'.$form_title.'.csv"');
	header('Content-Type: text/csv; charset=utf-8');
	header("Connection: close");

	$fp = fopen('php://output', 'w');

	fputcsv($fp, $header, ";");

	foreach ($sents as $sent) {
		if ( filter_result_out($sent['timestamp']) ) continue;

		$row = [];
		$row[] = date('j/m/Y @ H:i', $sent['timestamp']);

		foreach ($fields as $field) {
			if (is_array($sent[$field['attributes']['name']]) && isset($sent[$field['attributes']['name']][0]['name']) && isset($sent[$field['attributes']['name']][0]['size'])) {
				$sent[$field['attributes']['name']] = get_file_info($sent[$field['attributes']['name']], 'export');
			}

			$row[] = str_replace( array("\r\n", "\n", "\r"), array(" ", " ", " "), $sent[$field['attributes']['name']] );
		}

		fputcsv($fp, $row, ";");
	}

	fclose($fp);

	exit;
}

function delete_results() {

	if ( ! (isset($_REQUEST['delete-results']) && isset($_REQUEST['form'])) ) return;

	// Delete all the results if no filter active
	if ( empty($_REQUEST['results-from']) && empty($_REQUEST['results-until']) ) {
		delete_post_meta( $_REQUEST['form'], META_KEY_FORM_SUBMISSION );
	}
	else {
		$raw_sents = get_post_meta_db($_REQUEST['form'], META_KEY_FORM_SUBMISSION );
		foreach ($raw_sents as $sent) {
			if (filter_result_out($sent['meta_value']['timestamp']) ) continue;
			delete_metadata_by_mid( 'post', $sent['meta_id'] );
		}
	}

	wp_redirect(admin_url( '/edit.php?post_type=form&page=form_sent&form='.$_REQUEST['form']));
	exit;
}

function trash_result() {

	if ( ! (isset($_REQUEST['action']) && $_REQUEST['action'] === 'trash-result' && isset($_REQUEST['sent'])) ) return;

	delete_metadata_by_mid( 'post', $_REQUEST['sent'] );
	wp_redirect(admin_url( '/edit.php?post_type=form&page=form_sent&form='.$_REQUEST['form']));
	exit;
}

function get_months_interval( $timestamps ) {
	sort($timestamps);

	$start = (new DateTime());
	$start->setTimestamp($timestamps[0]);
	$start->modify('first day of this month');
	$start->setTime(0,0,0);

	$end = new DateTime();
	$end->setTimestamp(array_pop($timestamps));
	$end->modify('first day of this month');

	$interval = DateInterval::createFromDateString('1 month');
	$period = new DatePeriod($start, $interval, $end);

	$months = [];
	foreach ($period as $dt) {
		$months[] = $dt->getTimestamp();
	}

	return $months;
}

function filter_result_out( $timestamp ) : bool {
	global $results_until;

	if ( !empty($_REQUEST['results-until']) && empty($results_until) ) {
		$results_until = new DateTime();
		$results_until->setTimestamp((int)$_REQUEST['results-until']);
		$results_until->modify('last day of this month');
		$results_until->setTime(23,59,59);
		$results_until = $results_until->getTimestamp();
	}

	return (
		(!empty($_REQUEST['results-from']) && ($timestamp < (int)$_REQUEST['results-from']))
		|| (!empty($_REQUEST['results-until']) && $timestamp > $results_until)
	);
}

function register_results_page() {
	add_submenu_page(
		'edit.php?post_type=form',
		__('Forms submissions', 'supt'),
		__('Submissions', 'supt'),
		'edit_pages',
		'form_sent',
		__NAMESPACE__.'\render_page'
	);
}

function enqueue_admin_assets() {
	if ( get_current_screen()->id !== 'form_page_form_sent' ) return;

	wp_enqueue_style( 'form-result-page-style', SPCKFORMS_URI . '/assets/result-page.css', null, SPCKFORMS_PLUGIN_VERSION );
	wp_enqueue_script( 'form-result-page-script', SPCKFORMS_URI . '/assets/result-page.js', null, SPCKFORMS_PLUGIN_VERSION, true );
}

function render_page() {
	$context = [];

	$args = array(
		'posts_per_page'	=> -1,
		'post_type'				=> FORM_TYPE_NAME,
	);

	foreach ( get_posts($args) as $form) {

		$raw_sents = get_post_meta_db($form->ID, META_KEY_FORM_SUBMISSION );

		$isCurrentForm = ( isset($_REQUEST['form']) && $_REQUEST['form'] == $form->ID );
		$context['forms'][] = array(
			'id'    => $form->ID,
			'title' => get_the_title( $form->ID ),
			'count' => count($raw_sents),
			'active' => $isCurrentForm
		);

		if ( $isCurrentForm ) {
			$raw_fields = (array)get_field( META_KEY_FIELDS_NAME, $form->ID );

			$fields = array_map(
				function($f) {
					return array(
						'name'	=> $f['attributes']['name'],
						'label'	=> ( isset($f['attributes']['legend']) ? $f['attributes']['legend'] : $f['attributes']['label'] ),
						'type'	=> str_replace('supt/input-', '', $f['block']),
					);
				},
				$raw_fields
			);

			$results = [];
			$timestamps = [];
			foreach ($raw_sents as $item) {
				foreach ($item['meta_value'] as $name => $value) {
					if ( is_array($value) && isset($value[0]['name']) && isset($value[0]['size']) ) {
						$item['meta_value'][$name] = get_file_info($item['meta_value'][$name], 'display');
					}
				}

				$timestamps[] = $item['meta_value']['timestamp'];

				if ( filter_result_out($item['meta_value']['timestamp']) ) continue;

				$results[$item['meta_id']] = $item['meta_value'];
			}

			uasort($results, function($a, $b) {
				return $b['timestamp'] - $a['timestamp'];
			});

			if ( count($timestamps) > 0 )
				$context['months'] = get_months_interval($timestamps);

			$context['results_from'] = ( isset($_REQUEST['results-from']) ? (int)$_REQUEST['results-from'] : '' );
			$context['results_until'] = ( isset($_REQUEST['results-until']) ? (int)$_REQUEST['results-until'] : '' );

			$context['form']   = [
				'id'      => $form->ID,
				'title'   => get_the_title( $form->ID ),
				'fields'  => $fields,
				'results' => $results,
			];

			$context['user_can_export'] = true;
			$context['user_can_delete'] = user_can_access_admin_page();
		}
	}

	spckforms_get_template_part( 'result-list-page.php', $context );
}

/**
 * Add extra columns in edit table
 *
 * @param array $post_columns An array of column names.
 */
function register_columns_fields( $columns ) {

	$idxDate = array_search('date', array_keys($columns));

	return array_merge(
		array_slice( $columns, 0, $idxDate ),
		array( COLUMN_RESULTS_NAME => __( 'Submissions', 'supt' ) ),
		array_slice( $columns, $idxDate )
	);
}

/**
 * Populate extra columns with specific content, if available
 *
 * @param string $column_name The name of the column to display.
 * @param int    $post_id     The current post ID.
 */
function populate_columns_fields( $column_name, $post_id ) {
	if ( $column_name != COLUMN_RESULTS_NAME) return;

	$nbSubmissions = count( get_post_meta( $post_id, META_KEY_FORM_SUBMISSION ) );
	echo ( $nbSubmissions == 0
		? '-'
		: sprintf('<a href="%s">%d %s</a>',
				admin_url( '/edit.php?post_type=form&page=form_sent&form='.$post_id ),
				$nbSubmissions,
				_n( 'result', 'results', $nbSubmissions, 'supt' )
			)
	);
}


function get_file_info($files, $format = 'display') {

	if ( $format === 'display' ) {
		$result = sprintf(
			'<ul>%s</ul>',
			array_reduce($files, function($acc, $file) {
				if ( !empty($file) ) {
					$acc .= sprintf(
						'<li><a href="%s" target="_blank">%s (%s)</a></li>',
						esc_attr($file['url']),
						esc_attr($file['name']),
						esc_html($file['size'])
				);
				}
				return $acc;
			}, '')
		);
	}
	else {
		$result = implode(', ', array_reduce($files, function($acc, $file) {
			if ( !empty($file) ) {
				$acc[] = sprintf( '%s (%s)', $file['name'], $file['url'] );
			}
			return $acc;
		}, []));
	}

	return $result;
}
