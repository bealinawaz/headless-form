<?php
/**
 * Plugin Name:       Headless Form – Self-Hosted Form Backend via REST API
 * Plugin URI:        https://github.com/bealinawaz/headless-form
 * Description:       Receive form submissions from any website or app through a REST API endpoint. Submissions are saved in WordPress and emailed to you.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Ali Nawaz
 * Author URI:        https://bealinawaz.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       headless-form
 *
 * @package Headless_Form
 */

defined( 'ABSPATH' ) || exit;

define( 'HEADLESS_FORM_VERSION', '1.0.0' );
define( 'HEADLESS_FORM_FILE', __FILE__ );

require_once __DIR__ . '/includes/class-headless-form-settings.php';
require_once __DIR__ . '/includes/class-headless-form-submissions.php';
require_once __DIR__ . '/includes/class-headless-form-api.php';
require_once __DIR__ . '/includes/class-headless-form-privacy.php';

Headless_Form_Submissions::init();
Headless_Form_API::init();
Headless_Form_Privacy::init();

if ( is_admin() ) {
	require_once __DIR__ . '/includes/class-headless-form-admin.php';
	Headless_Form_Admin::init();
}
