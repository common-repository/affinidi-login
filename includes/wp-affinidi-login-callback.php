<?php

/**
 * This file is called when the auth param is found in the URL.
 */
defined('ABSPATH') or die('No script kiddies please!');

// Redirect the user back to the home page if logged in.
if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

// do a session a start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_options = new Affinidi_Login_Admin_Options();
$idtoken_parser = new Affinidi_Login_IDToken();

// default to homepage if the state not found or expired
$user_redirect = home_url();

// Not processing form or storing data.
// phpcs:disable WordPress.Security.NonceVerification.Recommended

// Check for error, ensure state has value
if (empty($_GET['state'])) {
    // redirect user with error code
    wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed_empty_state'), $user_redirect));
    exit;
}

// Authenticate Check and Redirect
if (!isset($_GET['code']) && !isset($_GET['error_description'])) {

    // Grab the state from the Auth URL and send to AL
    $state = sanitize_text_field($_GET['state']);

    // generate code verifier and challenge
    $verifier_bytes = bin2hex(openssl_random_pseudo_bytes(32));
    $code_verifier = rtrim(strtr(base64_encode($verifier_bytes), "+/", "-_"), "=");
    $challenge_bytes = hash("sha256", $code_verifier, true);
    $code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");

    // store the code verifier in the SESSION
    $_SESSION[$state] = $code_verifier;

    $params = [
        'oauth'                 => 'authorize',
        'response_type'         => 'code',
        'scope'                 => 'openid',
        'client_id'             => $admin_options->client_id,
        'redirect_uri'          => site_url('?auth=affinidi'),
        'state'                 => urlencode($state),
        'code_challenge'        => $code_challenge,
        'code_challenge_method' => 'S256',
    ];
    $params = http_build_query($params);
    wp_redirect(sanitize_url($admin_options->backend) . '/oauth2/auth?' . $params);
    exit;
}

// Check for error 
if (empty($_GET['code']) && !empty($_GET['error_description'])) {
    // redirect user with error code
    wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed'), $user_redirect));
    
    exit;
}

// grab the code
$auth_code = sanitize_text_field($_GET['code']);
// retrieve state and get the transient info for redirect
$state = sanitize_text_field($_GET['state']);
$redirect_to = get_transient("affinidi_user_redirect_to".$state);

// check if the state exists
if (!empty($redirect_to) && !empty($redirect_to[$state]) && !empty($redirect_to[$state]['redirect_to'])) {
    // set the redirect url based on state
    $user_redirect = sanitize_url($redirect_to[$state]['redirect_to']);
    // delete the transient after
    delete_transient("affinidi_user_redirect_to".$state);
}

// Check for error 
if (empty($auth_code) && !empty($_GET['error_description'])) {
    // redirect user with error code
    wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed'), esc_url($user_redirect)));
    exit;
}

// phpcs:enable WordPress.Security.NonceVerification.Recommended

// Handle the callback from the backend is there is one.
if (!empty($auth_code)) {

    $backend    = sanitize_url($admin_options->backend) . '/oauth2/token';

    // retrieve the code verifier from the SESSION
    $code_verifier = sanitize_text_field($_SESSION[$state]);

    $request_body = [
        'grant_type'    => 'authorization_code',
        'code'          => $auth_code,
        'client_id'     => $admin_options->client_id,
        'code_verifier' => $code_verifier,
        'redirect_uri'  => site_url('?auth=affinidi')
    ];

    $response = wp_remote_post( $backend, array(
            'method'      => 'POST',
            'body'        => $request_body
        )
    );

    if (is_wp_error($response)) {
        // redirect user with error code
        wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed'), esc_url($user_redirect)));
        exit;
    }

    $tokens = json_decode(wp_remote_retrieve_body($response));

    if (isset($tokens->error)) {
        // redirect user with error code
        wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed'), esc_url($user_redirect)));
        exit;
    }
    // parse ID Token from Affinidi Login response
    $id_token = $tokens->id_token;
    $info = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $id_token)[1]))), true);

    // extract user info
    $userInfo = $idtoken_parser->extract_user_info($info);
    // extract contact info
    $contactInfo = $idtoken_parser->extract_contact_info($info);
    
    $user_id = null;

    if (email_exists($userInfo['email']) == false) {
        if (affinidi_login_users_can_signup() == 0) {
            wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_only'), esc_url($user_redirect)));
            exit;
        }

        // Does not have an account... Register and then log the user in
        $random_password = wp_generate_password($length = 16, $extra_special_chars = true);
        $user_data = [
            'user_email'   => $userInfo['email'],
            'user_login'   => $userInfo['email'], // default to mail
            'user_pass'    => $random_password,
            'last_name'    => $userInfo['last_name'],
            'first_name'   => $userInfo['first_name'],
            'display_name' => (!empty($userInfo['display_name']) ? $userInfo['display_name'] : $userInfo['email']) // default to mail if not present
        ];

        $user_id = wp_insert_user($user_data);

        if (empty($user_id)) {
            // redirect user with error code
            wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed'), esc_url($user_redirect)));
            exit;
        }

        if (affinidi_login_wc_active()) {
            // instantiate WC Affinidi Login
            $affinidi_login_wc = new Affinidi_Login_WooCommerce($admin_options);
            // set Billing and Shipping Address from Vault
            $affinidi_login_wc->sync_customer_info($user_id, $userInfo, $contactInfo, true);
        }
        
        // Trigger new user created action so that there can be modifications to what happens after the user is created.
        // This can be used to collect other information about the user.
        do_action('affinidi_user_created', $userInfo, 1);

    } else {
        // Already Registered... Log the User In using ID or Email
        $user = get_user_by('email', $userInfo['email']);

        /*
         * Added just in case the user is not used but the email may be. If the user returns false from the user ID,
         * we should check the user by email. This may be the case when the users are preregistered outside of OAuth
         */
        if (!$user) {
             // Get the user by email using login
            $user = get_user_by('login', $userInfo['email']);
        }

        if (!$user) {
            // redirect user with error code
            wp_safe_redirect(add_query_arg(array('message' => 'affinidi_login_failed'), esc_url($user_redirect)));
            exit;
        }

        $user_id = $user->ID;

        if (affinidi_login_wc_active()) {
            // instantiate WC Affinidi Login
            $affinidi_login_wc = new Affinidi_Login_WooCommerce($admin_options);
            // set Billing and Shipping Address from Vault
            $affinidi_login_wc->sync_customer_info($user_id, $userInfo, $contactInfo, false);
        }

        // Trigger action when a user is logged in.
        // This will help allow extensions to be used without modifying the core plugin.
        do_action('affinidi_user_login', $userInfo, 1);
    }

    // Did we retrieved or created the user successfully?
    if (!empty($user_id)) {
        // set current user session
        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        if (is_user_logged_in()) {
            wp_safe_redirect($user_redirect);
            exit;
        }
    }
}
