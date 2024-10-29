<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Add login button for affinidi on the login form.
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/login_form
 */
function affinidi_login_form_button()
{

    $state = sanitize_text_field(affinidi_get_user_redirect_url());

    $affinidi_login_button = sprintf('
        <div class="affinidi-login-wrapper">
            <p style="text-align:center; margin:5px;">Log in <b>passwordless</b> with</p>
            <a style="margin:1em auto;" rel="nofollow" class="button" id="affinidi-login-m"
            href="%s">Affinidi Login</a>
            <div style="clear:both;"></div>
        </div>',
        esc_url(site_url('?auth=affinidi&state=' . $state))
    );

    echo wp_kses_post($affinidi_login_button);
}
// Display the Affinidi Login button at the top of the WP Login form
add_action('login_message', 'affinidi_login_form_button');

/**
 * Login Button Shortcode
 *
 * @param  [type] $atts [description]
 *
 * @return [type]       [description]
 */
function affinidi_login_button_shortcode($atts = array())
{

    if (is_user_logged_in()) {
        return;
    }

    $state = sanitize_text_field(affinidi_get_user_redirect_url());

    $a = shortcode_atts([
        'title'  => 'Affinidi Login',
        'class'  => 'affinidi-login',
        'target' => '_self',
        'text'   => 'Affinidi Login'
    ], $atts);

    $affinidi_login_button_shortcode = sprintf(
        '<a id="affinidi-login-m" rel="nofollow" 
        class="%s" 
        href="%s" 
        title="%s" 
        target="%s">%s</a>',
        esc_attr($a['class']),
        esc_url(site_url('?auth=affinidi&state=' . $state)),
        esc_attr($a['title']),
        esc_attr($a['target']),
        esc_html($a['text'])
    );

    return wp_kses_post($affinidi_login_button_shortcode);
}

add_shortcode('affinidi_login', 'affinidi_login_button_shortcode');

/**
 * Get user login redirect.
 * Just in case the user wants to redirect the user to a new url.
 *
 * @return string
 */
function affinidi_get_user_redirect_url(): string
{
    // Global WP instance
    global $wp;

    $admin_options = new Affinidi_Login_Admin_Options();

    // Homepage as default redirect
    $redirect_url = home_url();

    // Redirect users if directly logging-in from wp-login.php form or redirect to dashboard option is set
    if ( $GLOBALS['pagenow'] == 'wp-login.php' ) {
        $redirect_url = admin_url();
    }

    // Not processing form.
    // phpcs:disable WordPress.Security.NonceVerification.Recommended

    // Check if we are passing redirect_to value, use it
    if ( isset( $_REQUEST['redirect_to'] ) ) {
        $redirect_url = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) );
    } else {
        // Get the current page of the user where the button is triggered (if redirect to dashboard is not set)
        if ( $admin_options->redirect_user_origin == 1) {
            if ( ! empty( $wp->request ) ) {
                if ( ! empty( $wp->did_permalink ) && $wp->did_permalink == true ) {
                    // build url from the current page with query strings attached
                    $redirect_url = home_url( add_query_arg( $_GET, trailingslashit( $wp->request ) ) );
                } else {
                    $redirect_url = home_url( add_query_arg( null, null ) );
                }
            } else {
                // homepage with query strings
                if ( ! empty( $wp->query_string ) ) {
                    $redirect_url = home_url( '?' . $wp->query_string );
                }
            }
        }
    }

    // phpcs:enable WordPress.Security.NonceVerification.Recommended

    // generate random state
    $state = md5( wp_rand() . microtime( true ) );
    // store redirect_to transient info to options
    $affinidi_state_values = array(
        $state => array(
            'redirect_to' => sanitize_url($redirect_url)
        )
    );
    set_transient("affinidi_user_redirect_to" . $state, $affinidi_state_values, 300);

    return $state;

}

function affinidi_login_users_can_signup() {
    return is_multisite() ? users_can_register_signup_filter() : get_site_option( 'users_can_register' );
}

/**
 * Check if WooCommerce is activated
 */
if ( ! function_exists( 'affinidi_login_wc_active' ) ) {
	function affinidi_login_wc_active() {
		if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
	}
}

// do we have active WooCommerce?
if (affinidi_login_wc_active()) {
    $affinidi_login_wc = new Affinidi_Login_WooCommerce( new Affinidi_Login_Admin_Options() );
    // display buttons
    $affinidi_login_wc->filter_display_affinidi_login_button();
}