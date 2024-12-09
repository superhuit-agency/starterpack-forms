<?php

/**
 * Plugin Name:       Starterpack Forms
 * Plugin URI:        https://github.com/superhuit-agency/starterpack-forms
 * Description:       Starterpack's Form Custom Post Type.
 * Version:           1.1.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            superhuit <tech@superhuit.ch>
 * Author URI:        https://profiles.wordpress.org/superhuit/
 * License:           GNU General Public License v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       spckforms
 *
 * Starterpack i18n is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Starterpack i18n is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Starterpack i18n. If not, see {URI to Plugin License}.
 */

defined('ABSPATH') or die('Cheatin&#8217; uh?');

define('SPCKFORMS_PLUGIN_VERSION', '1.1.0');
define('SPCKFORMS_PATH', __DIR__);
define('SPCKFORMS_URI', plugin_dir_url(__FILE__));

// Load dependencies
// ====
if (! file_exists(__DIR__ . '/vendor/autoload.php')) {
	add_action('admin_notices', function () {
?>
		<div class="notice notice-warning">
			<p><?php _e('Please install composer dependencies for Starterpack Form CPT to work', 'spckforms'); ?></p>
		</div>
<?php
	});
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/helpers/_loader.php';

require_once __DIR__ . '/form-type.php';
require_once __DIR__ . '/result-list.php';
require_once __DIR__ . '/submission.php';

// ====
// Action & filter hooks
// ====
register_activation_hook(__FILE__, 'spckforms_activate');
register_deactivation_hook(__FILE__, 'spckforms_deactivate');
register_uninstall_hook(__FILE__, 'spckforms_uninstall');

// Load translation texts
add_action('after_setup_theme', function () {
	load_theme_textdomain('spckforms', SPCKFORMS_PATH . '/languages');
});

/**
 * Execute anything necessary on plugin activation
 */
function spckforms_activate()
{
	// e.g. Save default options to database
}

/**
 * Execute anything necessary on plugin deactivation
 */
function spckforms_deactivate()
{
	// e.g. delete cache or temp options
}

/**
 * Execute anything necessary on plugin uninstall (deletion)
 */
function spckforms_uninstall()
{
	// e.g. remove plugin options from database
}
