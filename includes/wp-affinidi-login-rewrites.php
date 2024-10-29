<?php

// ABSPATH prevent public user to directly access your .php files through URL.
defined('ABSPATH') or die('No script kiddies please!');

/**
 * Class Rewrites
 *
 */
class Affinidi_Login_Rewrites
{

    private $admin_options;

    public function __construct(Affinidi_Login_Admin_Options $admin_options)
    {
        $this->admin_options = new Affinidi_Login_Admin_Options();
    }

    public function create_rewrite_rules($rules): array
    {
        global $wp_rewrite;
        $newRule  = ['auth/(.+)' => 'index.php?auth=' . $wp_rewrite->preg_index(1)];
        $newRules = $newRule + $rules;

        return $newRules;
    }

    public function add_query_vars($qvars): array
    {
        $qvars[] = 'auth';
        $qvars[] = 'code';
        $qvars[] = 'message';
        return $qvars;
    }

    public function flush_rewrite_rules()
    {
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }

    public function template_redirect_intercept(): void
    {   
        global $wp_query;
        $auth = sanitize_text_field($wp_query->get('auth'));

        if ($auth !== '') {
            // affinidi will add another ? to the uri, this will make the value of auth like this : affinidi?code=c9550137370a99bc2137
            $matches = [];
            preg_match('/^([a-zA-Z]+)(\?code=[a-zA-Z0-9]+)?$/', $auth, $matches);
            if (count($matches) == 3 && $matches[1] == 'affinidi') {
                $tmp = explode('=', $matches[2]);
                if ($tmp[0] == '?code') {
                    $url =  home_url("?auth=affinidi&code={$tmp[1]}");
                    wp_redirect($url);
                    exit;
                }
            }
        }

        global $pagenow;
        $message = sanitize_text_field($wp_query->get('message'));
        if ($pagenow == 'index.php' && isset($message)) {
            require_once(AFFINIDI_PLUGIN_DIR . '/templates/wp-affinidi-login-error-msg.php');
        }

        if ($auth == 'affinidi') {
            require_once(AFFINIDI_PLUGIN_DIR . '/includes/wp-affinidi-login-callback.php');
            exit;
        }
    }
}

$rewrites = new Affinidi_Login_Rewrites(new Affinidi_Login_Admin_Options());

add_filter('rewrite_rules_array', [$rewrites, 'create_rewrite_rules']);
add_filter('query_vars', [$rewrites, 'add_query_vars']);
add_filter('wp_loaded', [$rewrites, 'flush_rewrite_rules']);
add_action('template_redirect', [$rewrites, 'template_redirect_intercept']);
