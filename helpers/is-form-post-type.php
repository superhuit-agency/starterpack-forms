<?php

namespace SUPT\StarterpackForms\Helpers;

use const SUPT\StarterpackForms\Type\NAME as FORM_TYPE_NAME;

/**
 *
 * @param integer|WP_Post
 * @return boolean
 */
function is_form_post_type($post) {
	return ( get_post_type($post) === FORM_TYPE_NAME );
}
