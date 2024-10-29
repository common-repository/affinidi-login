<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class affinidi_admin
 */
class Affinidi_Login_Admin_Settings
{
    const OPTIONS_NAME = 'affinidi_options';

    private $admin_options;

    private $option_name;

    private $admin_settings_fields = array(
        'client_id',
        'backend',
        'redirect_user_origin',
        'ecommerce_sync_address_info',
        'ecommerce_show_al_button',
        'affinidi_login_loginform_header',
        'affinidi_login_regform_header'
    );

    public function __construct(Affinidi_Login_Admin_Options $options) 
    {
        $this->admin_options = $options;
        $this->option_name = $this->admin_options->get_option_name();
    }

    public static function init(Affinidi_Login_Admin_Options $options)
    {
        $admin_settings = new self($options);
        // add_action adds a callback function to an action hook.
        // admin_init fires as an admin screen or script is being initialized.
        add_action('admin_init', [$admin_settings, 'admin_init']);
        // admin_menu fires before the administration menu loads in the admin.
        // This action is used to add extra submenus and menu options to the admin panel’s menu structure. It runs after the basic admin panel menu structure is in place.
        add_action('admin_menu', [$admin_settings, 'add_page']);
    }

    public function get_admin_settings() 
    {
        return $this->admin_settings_fields;
    }

    /**
     * [admin_init description]
     *
     * @return [type] [description]
     */
    public function admin_init()
    {
        // A callback function that sanitizes the option's value
        register_setting('affinidi_options', $this->option_name, [$this, 'validate']);
    }

    /**
     * Add affinidi submenu page to the settings main menu
     */
    public function add_page()
    {
        add_options_page('Affinidi Login', 'Affinidi Login', 'manage_options', 'affinidi_settings', [$this, 'options_do_page']);
    }

    /**
     * [options_do_page description]
     *
     * @return [type] [description]
     */
    public function options_do_page()
    {
        ?>
        <div class="affinidi-login-settings container-fluid">
            <div class="admin-settings-header">
                <h1>Affinidi Login</h1>
                <a class="affinidi-login-doc" href="https://docs.affinidi.com/labs/3rd-party-plugins/passwordless-authentication-for-wordpress/" target="_blank">
                    Documentation
                </a>
            </div>
            <div class="admin-settings-inside">
                <p>This plugin is meant to be used with <a href="https://www.affinidi.com/product/affinidi-login" target="_blank">Affinidi Login</a> and uses <a href="https://oauth.net/2/pkce/" target="_blank">PKCE</a> extension of OAuth 2.0 standard.</p>
                <p>
                    <strong>NOTE:</strong> If you want to add a
                    custom link anywhere in your theme, simply link to
                    <code><?php echo esc_url(site_url('?auth=affinidi')); ?></code> or use the shortcode <code>[affinidi_login]</code>
                    if the user is not logged in.
                </p>
                <div id="accordion">
                    <h3>Step 1: Setup</h3>
                    <div>
                        <strong>Create a Login Configuration</strong>
                        <ol>
                            <li>Login to <a
                                        href="https://portal.affinidi.com" target="_blank">Affinidi Portal</a> and go to the Affinidi Login service.
                            </li>
                            <li>Create a Login Configuration and set the following fields:
                                <p>
                                <strong>Redirect URIs:</strong>
                                <code><?php echo esc_url(site_url('?auth=affinidi')); ?></code></p>
                                <p>
                                <strong>Auth method:</strong> <code>None</code></p>
                            </li>
                            <li>Copy the <strong>Client ID</strong> and <strong>Issuer URL</strong> and paste it in Step 2 below.</li>
                            <li>
                                <p>Modify the <strong>Presentation Definition</strong> and <strong>ID Token Mapping</strong> using <a href="https://docs.affinidi.com/labs/3rd-party-plugins/passwordless-authentication-for-wordpress/#presentation-definition-and-id-token-mapping" target="_blank">this template.</a></p>
                                <p><em>If you have activated a supported E-Commerce plugin on this WordPress site, use the template for E-Commerce.</em></p>
                            </li>
                        </ol>
                    </div>
                    <h3 id="sso-configuration">Step 2: Configure</h3>
                    <div class="row">
                        <form method="post" action="options.php">
                            <?php settings_fields('affinidi_options'); ?>
                            <table class="form-table">
                                <tr valign="top">
                                    <th scope="row">Client ID</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[client_id]" min="10"
                                            value="<?php echo esc_html($this->admin_options->client_id); ?>"/>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Issuer URL</th>
                                    <td>
                                        <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[backend]" min="10"
                                            value="<?php echo esc_html($this->admin_options->backend); ?>"/>
                                        <p class="description">Example: https://[YOUR_PROJECT_ID].apse1.login.affinidi.io</p>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row">Redirect user to Origin Page</th>
                                    <td>
                                        <input type="checkbox"
                                            name="<?php echo esc_html($this->option_name); ?>[redirect_user_origin]"
                                            value="1" <?php echo esc_html($this->admin_options->redirect_user_origin) == 1 ? 'checked="checked"' : ''; ?> />
                                        <p class="description">By default, users will be redirected to Homepage. If the user used the <em>wp-login.php</em> form, they will be redirected to Dashboard.</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">Restrict user login flow</th>
                                    <td>
                                        <?php
                                        if (affinidi_login_users_can_signup()) {
                                        ?>
                                        <p class="description">Signup is currently <strong>enabled</strong> in the WordPress General Settings.</p>
                                        <p class="description">Update the WordPress settings if you wish to restrict users from signing up using their Vault.</p>
                                        <?php
                                        } else {
                                        ?>
                                        <p class="description">Sign up is currently <strong>disabled</strong> in the WordPress General Settings.</p>
                                        <p  class="description">Update the WordPress settings if you wish to allow users to signup using their Vault.</p>
                                        <?php
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                    </div>
                    <hr />
                    <?php

                    if (affinidi_login_wc_active()) {
                    ?>
                    <h3 id="sso-configuration">WooCommerce Settings</h3>
                    <div class="row">
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Sync customer profile from Vault</th>
                                <td>
                                    <select name="<?php echo esc_html($this->option_name); ?>[ecommerce_sync_address_info]">
                                        <option value="onlysignup" <?php selected( $this->admin_options->ecommerce_sync_address_info, "onlysignup" ); ?>>Only on sign up</option>
                                        <option value="billing" <?php selected( $this->admin_options->ecommerce_sync_address_info, "billing" ); ?>>Always sync Billing info</option>
                                        <option value="billing_shipping" <?php selected( $this->admin_options->ecommerce_sync_address_info, "billing_shipping" ); ?>>Always sync Billing and Shipping info</option>
                                    </select>
                                    <p class="description">Select whether to sync the user profile from Vault whenever the user logs in to their WooCommerce account or only sync their profile on sign-up. Sign-up will populate the customer billing and shipping address info.</p>
                                    <p class="description">Remember to modify the <strong>Presentation Definition</strong> and <strong>ID Token Mapping</strong> using the <a href="https://docs.affinidi.com/labs/3rd-party-plugins/passwordless-authentication-for-wordpress/#presentation-definition-and-id-token-mapping" target="_blank">E-Commerce template</a> to request the user profile from Affinidi Vault.</p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Display Affinidi Login button</th>
                                <td>
                                    <select name="<?php echo esc_html($this->option_name); ?>[ecommerce_show_al_button]">
                                        <option value="top_form" <?php selected( $this->admin_options->ecommerce_show_al_button, "top_form" ); ?>>At the top of the Login & Registration Form</option>
                                        <option value="bottom_form" <?php selected( $this->admin_options->ecommerce_show_al_button, "bottom_form" ); ?>>At the bottom of the Login & Registration Form</option>
                                        <option value="" <?php selected( $this->admin_options->ecommerce_show_al_button, "" ); ?>>Use shortcode to display the button</option>
                                    </select>
                                    <p class="description">If you choose <em>"Use shortcode to display the button"</em>, use the shortcode <code>[affinidi_login]</code> and manually edit your desired page to display the button.</p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Affinidi Login button header (Login Form)</th>
                                <td>
                                    <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[affinidi_login_loginform_header]" min="10"
                                        value="<?php 
                                        $text_value = empty($this->admin_options->affinidi_login_loginform_header) ? "Log in passwordless with" : $this->admin_options->affinidi_login_loginform_header;

                                        echo esc_attr($text_value);
                                        ?>"/>
                                    <p class="description">Displays at the top of the Affinidi Login button in the Login Form of WooCommerce.</p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">Affinidi Login button header (Registration Form)</th>
                                <td>
                                    <input type="text" class="regular-text" name="<?php echo esc_html($this->option_name); ?>[affinidi_login_regform_header]" min="10"
                                        value="<?php 
                                        $text_value = empty($this->admin_options->affinidi_login_regform_header) ? "Sign up seamlessly with" : $this->admin_options->affinidi_login_regform_header;

                                        echo esc_attr($text_value);
                                        ?>"/>
                                        <p class="description">Displays at the top of the Affinidi Login button in the Registration Form of WooCommerce.</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    } else { ?>
                    <p class="description">There's no active supported e-commerce plugin configured on this WordPress site. E-Commerce Settings is disabled. To learn more about the supported e-commerce plugins, <a href="https://docs.affinidi.com/labs/3rd-party-plugins/passwordless-authentication-for-wordpress/#supported-e-commerce-plugins" target="_blank">click here.</a></p>
                    <?php
                    }
                    ?>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php esc_html_e('Save Changes', 'affinidi-login') ?>"/>
                    </p>
                    </form>
                </div>
            </div>
        </div>
        <div style="clear:both;"></div>
        <?php
    }

    /**
     * Settings Validation
     *
     * @param array $input option array
     *
     * @return array
     */
    public function validate(array $input)
    {
        $admin_settings = $this->get_admin_settings();
        $options = array();

		foreach ( $admin_settings as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$options[ $field ] = sanitize_text_field( trim( $input[ $field ] ) );
			} else {
				$options[ $field ] = '';
			}
		}

		return $options;
    }
}

$admin_options = new Affinidi_Login_Admin_Options();

Affinidi_Login_Admin_Settings::init($admin_options);
