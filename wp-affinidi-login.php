<?php
 /**
 * Affinidi Login - Passwordless Authentication
 *
 * A paradigm shift in the registration and sign-in process, Affinidi Login is a game-changing solution for developers. 
 * With our revolutionary passwordless authentication solution your user's first sign-in doubles as their registration, and all the necessary data for onboarding can be requested during this streamlined sign-in/signup process. 
 * End users are in full control, ensuring that they consent to the information shared in a transparent and user-friendly manner. 
 * This streamlined approach empowers developers to create efficient user experiences with data integrity, enhanced security and privacy, and ensures compatibility with industry standards.
 *
 * @package   Affinidi_Login
 * @category  General
 * @author    Affinidi
 * @copyright 2024 Affinidi
 * @license   https://github.com/affinidi/wordpress-affinidi-login/blob/main/LICENSE
 * @link      https://www.affinidi.com/product/affinidi-login
 *
 * @wordpress-plugin
 * Plugin Name:       Affinidi Login - Passwordless Authentication
 * Plugin URI:        https://github.com/affinidi/wordpress-affinidi-login
 * Description:       A paradigm shift in the registration and sign-in process, Affinidi Login is a game-changing solution for developers. With our revolutionary passwordless authentication solution your user's first sign-in doubles as their registration, and all the necessary data for onboarding can be requested during this streamlined sign-in/signup process. End users are in full control, ensuring that they consent to the information shared in a transparent and user-friendly manner. This streamlined approach empowers developers to create efficient user experiences with data integrity, enhanced security and privacy, and ensures compatibility with industry standards.
 * Version:           1.1.2
 * Requires at least: 6.4
 * Tested up to:      6.5
 * Requires PHP:      7.4
 * Author:            Affinidi
 * Author URI:        https://affinidi.com
 * Text Domain:       affinidi-login
 * License:           MIT
 * License URI:       https://github.com/affinidi/wordpress-affinidi-login/blob/main/LICENSE
 */

 // ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

if (!defined('AFFINIDI_PLUGIN_DIR')) {
    define('AFFINIDI_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
}

// Require the main plugin class
require_once(AFFINIDI_PLUGIN_DIR . 'Affinidi.php');

$affinidi = new Affinidi();

add_action('admin_menu', [$affinidi, 'plugin_init']);
add_action( 'admin_enqueue_scripts', [$affinidi, 'affinidi_login_enqueue_admin_scripts'] );
add_action( 'wp_enqueue_scripts', [$affinidi, 'affinidi_login_enqueue_fe_scripts'] );
add_action( 'login_enqueue_scripts', [$affinidi, 'affinidi_login_enqueue_fe_scripts'] );
add_action('wp_logout', [$affinidi, 'logout']);

register_activation_hook(__FILE__, [$affinidi, 'setup']);
register_activation_hook(__FILE__, [$affinidi, 'upgrade']);
