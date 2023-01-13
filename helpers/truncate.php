<?php

namespace SUPT\StarterpackForms\Helpers;

/**
 * Truncate a text to a given char number
 * It truncates at the last white space so it doesn't cut in the middle of a word
 * Appends … at the end of the truncated text
 *
 * @param string $text The text to truncate
 * @param int $chars Optional. Default: 25. The number max of chars
 *
 * @return string The truncated text if longer than $chars
 */
function truncate($text, $chars = 25) {
	// Bail early
	if (strlen($text) <= $chars) return $text;

	$text = $text.' ';
	$text = substr($text,0,$chars);
	$text = substr($text,0,strrpos($text,' '));

	return $text."…";
}
