<?php

namespace SUPT\StarterpackForms\Helpers;

/**
 * @source https://wordpress.stackexchange.com/a/121461/86838
 */

/**
 * Alternative to get_post_meta(), to retrieve meta_ids. @see get_meta_db()
 */
function get_post_meta_db( $post_id, $meta_key = null, $single = false, $meta_val = null, $output = ARRAY_A ){
	return get_meta_db( 'post', $post_id, $meta_key, $meta_val, $single, $output );
}

/**
* Alternative to get_metadata(). Differences:
*  - returns every meta field (instead of only meta_values)
*  - bypasses meta filters/actions
*  - queries database, bypassing cache
*  - returns raw meta_values (instead of unserializing arrays)
*
* @param string $meta_type Type of object metadata is for (e.g., comment, post, or user)
* @param int    $object_id ID of the object metadata is for
* @param string $meta_key  Optional. Metadata key to retrieve. By default, returns all metadata for specified object.
* @param mixed  $meta_val  Optional. If specified, will only return rows with this meta_value.
* @param bool   $single    Optional. If true, returns single row, else returns array of rows.
* @param string $output    Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. @see wpdb::get_results()
*
* @return array Single metadata row, array of rows, empty array if no matches, or false if there was an error.
*/
function get_meta_db( $meta_type, $object_id = null, $meta_key = null, $meta_val = null, $single = false, $output = ARRAY_A ){

	if( !$meta_type || !$table = _get_meta_table( $meta_type ) )
			return false;

	// Build query
	global $wpdb;
	$query_string = "SELECT * FROM $table";
	// Add passed conditions to query
	$where = array();
	$params = array();
	if( $object_id = absint( $object_id ) ) {
		$where[] = sanitize_key( $meta_type.'_id' ).' = %d';
		$params[] = $object_id;
	}
	if( !empty($meta_key) ) {
		$where[] = 'meta_key = %s';
		$params[] = wp_unslash( $meta_key );
	}
	if( null !== $meta_val ) {
		$where[] = 'meta_value = %s';
		$params[] = maybe_serialize(wp_unslash($meta_val));{}
	}

	if( !empty($where) ) $query_string .= ' WHERE '.implode(' AND ', $where );
	if( $single ) $query_string .= ' LIMIT 1';

	$query = $wpdb->prepare( $query_string, $params);
	$rows = $wpdb->get_results( $query, $output );

	if( empty( $rows ) )
		return ( $single ? null : array() );

	// Unserialize serialized meta_values
	$rows = array_map(function($row) use ($output){

		if ( $output==ARRAY_A ) $row['meta_value'] = maybe_unserialize( $row['meta_value'] );
		else if ($output==ARRAY_N) $row[3] = maybe_unserialize( $row[3] );
		else $row->meta_value = maybe_unserialize( $row->meta_value );

		return $row;
	}, $rows);

	return ( $single ? reset( $rows ) : $rows );
}
