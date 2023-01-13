<?php

namespace SUPT\StarterpackForms\Helpers;

/**
 *
 * @source https://stackoverflow.com/a/30342756
 */
function rearrange_upload_array(array $array) {
	if(!is_array(reset($array)))
		return $array;

	$rearranged = [];
	foreach($array as $property => $values)
		foreach($values as $key => $value)
			$rearranged[$key][$property] = $value;

	foreach($rearranged as &$value)
		$value = rearrange_upload_array($value);

	return $rearranged;
}
