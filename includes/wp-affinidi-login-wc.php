<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

class Affinidi_Login_WooCommerce {

    private $admin_options;

	public function __construct(Affinidi_Login_Admin_Options $options) 
    {
        $this->admin_options = $options;
    }

    function set_wc_billing_address(& $customer, $userInfo, $contactInfo) 
    {
        // set billing info
        $customer->set_billing_first_name($userInfo['first_name']);
        $customer->set_billing_last_name($userInfo['last_name']);
        $customer->set_billing_email($userInfo['email']);
        $customer->set_billing_phone($contactInfo['phone']);

        $customer->set_billing_address($contactInfo['address_1']);
        $customer->set_billing_city($contactInfo['city']);
        $customer->set_billing_state($contactInfo['state']);
        $customer->set_billing_postcode($contactInfo['postcode']);
        $customer->set_billing_country($contactInfo['country']);
    }

    function set_wc_shipping_address(& $customer, $userInfo, $contactInfo) 
    {
        // set billing info
        $customer->set_shipping_first_name($userInfo['first_name']);
        $customer->set_shipping_last_name($userInfo['last_name']);
        $customer->set_shipping_phone($contactInfo['phone']);

        $customer->set_shipping_address($contactInfo['address_1']);
        $customer->set_shipping_city($contactInfo['city']);
        $customer->set_shipping_state($contactInfo['state']);
        $customer->set_shipping_postcode($contactInfo['postcode']);
        $customer->set_shipping_country($contactInfo['country']);
    }

    public function sync_customer_info($customerId, $userInfo, $contactInfo, $isSignup) 
    {
        // Get the WC_Customer instance object from user ID
        $customer = new WC_Customer( $customerId );

        // sync address info from Vault
        if ($isSignup || $this->admin_options->ecommerce_sync_address_info != "billing") {
            $this->set_wc_billing_address($customer, $userInfo, $contactInfo);
            $this->set_wc_shipping_address($customer, $userInfo, $contactInfo);
        } else {
            $this->set_wc_billing_address($customer, $userInfo, $contactInfo);
        }

        // save customer data
        $customer->save();

    }

    public function filter_affinidi_login_wc_login() 
    {
        $affinidi_login_form_button = sprintf(
            '<div class="form-affinidi-login">
            <div><p class="form-affinidi-login-header">%s</p></div>
            <div>%s</div>
            </div>',
            esc_html($this->admin_options->affinidi_login_loginform_header),
            affinidi_login_button_shortcode()
        );
    
        echo wp_kses_post($affinidi_login_form_button);
    }

    public function filter_affinidi_login_wc_registration() 
    {
        $affinidi_login_form_button = sprintf(
            '<div class="form-affinidi-login">
            <div><p class="form-affinidi-login-header">%s</p></div>
            <div>%s</div>
            </div>',
            esc_html($this->admin_options->affinidi_login_regform_header),
            affinidi_login_button_shortcode()
        );
    
        echo wp_kses_post($affinidi_login_form_button);
    }

    public function filter_display_affinidi_login_button() 
    {
        if ($this->admin_options->ecommerce_show_al_button == "") {
            // do nothing
            return;
        }

        $login_button_position = $this->admin_options->ecommerce_show_al_button == 'top_form' ? 'woocommerce_login_form_start' : 'woocommerce_login_form_end';
        $reg_button_position = $this->admin_options->ecommerce_show_al_button == 'top_form' ? 'woocommerce_register_form_start' : 'woocommerce_register_form_end';

        add_filter( $login_button_position, array($this, 'filter_affinidi_login_wc_login') );
        add_filter( $reg_button_position, array($this, 'filter_affinidi_login_wc_registration') );
    }
}
