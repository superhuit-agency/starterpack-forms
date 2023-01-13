<?php

namespace SUPT\StarterpackForms\Helpers;

// used to protect sending forms from unauthorized clients
function get_forms_secret() {
	if (!empty($_ENV['WORDPRESS_FORMS_SECRET'])) {
		return $_ENV['WORDPRESS_FORMS_SECRET'];
	}

	$opt_form_secret = get_option('forms_secret');
	if (!empty($opt_form_secret)) return $opt_form_secret;

	return 'spck';
}


function get_hcaptcha_secret() {
	if (!empty($_ENV['WORDPRESS_HCAPTCHA_SECRET'])) {
		return $_ENV['WORDPRESS_HCAPTCHA_SECRET'];
	}

	$opt_hcaptcha_form_secret = get_option('forms_hcaptcha_secret');
	if (!empty($opt_hcaptcha_form_secret)) return $opt_hcaptcha_form_secret;

	return false;
}
