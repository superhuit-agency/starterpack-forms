<?php

namespace SUPT\StarterpackForms\Helpers;

/**
 * Allows to override the template in the theme or child theme
 * @see https://deliciousbrains.com/wordpress-plugin-development-template-files/#wordpress-way
 *
 * @param string $slug The slug name for the template.
 * @param array  $args Optional. Additional arguments passed to the template.
 *                     Default empty array.
 * @param bool   $echo Whether to echo or return the form.
 *                     Default true.
 * @return void|string Void if 'echo' argument is true, HTML string if 'echo' is false.
 */
function get_template_part($slug, $args = [], $echo = true) {
	$template_path = locate_template( $slug );
	if ( empty($template_path) ) $template_path = SPCKFORMS_PATH ."/templates/$slug";

	if ( !$echo ) ob_start();
	load_template( $template_path, true, $args );
	if ( !$echo ) return ob_get_clean();
}
