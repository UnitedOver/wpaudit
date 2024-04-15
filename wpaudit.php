<?php

/*
 * Plugin Name: WPAudit
 * Description: Enhance the security of your WordPress website with a plugin designed to verify the integrity of your installed plugins
 * Version: 1.0
 * Plugin URI: https://wpaudit.unitedover.com/
 * Author URI: https://www.unitedover.com/
 * Author: UnitedOver
 * Text Domain: wpaudit
 * Requires PHP: 5.5
 * Domain Path: /languages
 * Update URI: https://wpaudit.unitedover.com/
 */

use WPAudit\core\HashVerifier;
use WPAudit\core\renderer\Settings;

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'core/class-menu.php';
require_once plugin_dir_path(__FILE__) . 'core/renderer/class-settings.php';


function wpaudit_version()
{
    return '1.0';
}

function wpaudit()
{

    \WPAudit\core\Menu::admin_menu();
}

function wpaudit_supported_plugins()
{
    $response = wp_remote_request(Settings::hash_base_url() . 'plugins.json');

    if ($response instanceof WP_Error) {
        wp_die($response->get_error_message());
    }

    $response_object = $response['http_response']->get_response_object();
    $status_code = $response_object->status_code;

    if ($status_code != 200) {
        $error = esc_attr__('Error occurred while fetching plugins list, Status Code:%s', 'wpaudit');
        wp_die(sprintf($error, $status_code));
    }

    $plugins_list = $response_object->body;

    return json_decode($plugins_list, JSON_OBJECT_AS_ARRAY);
}

function wpaudit_verify_plugin()
{
    require_once plugin_dir_path(__FILE__) . 'core/class-hashverifier.php';
    $verifier = new HashVerifier();
    $verifier->process_request($_REQUEST);
}

add_action('wp_ajax_wpaudit_verify_plugin', 'wpaudit_verify_plugin');

function get_wpaudit_asset_uri($path)
{
    return plugins_url($path, __FILE__);
}

wpaudit();