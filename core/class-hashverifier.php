<?php

namespace wpaudit\core;

use WP_Error;
use WPAudit\core\renderer\Settings;

defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . '/class-hashgenerator.php';

class HashVerifier
{
    public function __construct()
    {

    }

    public function process_request($request)
    {
        if (!current_user_can('edit_plugins')) {
            $this->show_error('Error');
        }
        $error = false;

        if (empty($request['plugin'])) {
            $error = new WP_Error('plugin-empty', __('Plugin slug cannot be empty!', 'wpaudit'));
        }

        if (empty($request['version'])) {
            $error = new WP_Error('version-empty', __('Plugin version cannot be empty!', 'wpaudit'));
        }

        if (!wp_verify_nonce($request['nonce'], Settings::ACTION)) {
            $error = new WP_Error('plugin-slug-empty', __('Please reload the page and try again!', 'wpaudit'));
        }

        if ($error instanceof WP_Error) {
            $this->show_error($error->get_error_message());
        }

        $plugin_slug = $request['plugin'];
        $plugin_version = $request['version'];
        $this->verify_hash($plugin_slug, $plugin_version);
    }

    public function show_error($message)
    {
        $this->show_message('error', $message);
    }

    public function show_message($type, $message)
    {
        $data = array();

        ob_start();
        $this->message_html($type, $message);
        $data['html'] = ob_get_clean();
        ob_end_clean();
        wp_send_json($data);
    }

    public function message_html($type, $message)
    {
        $message_class = '';
        switch ($type) {
            case 'error':
                $message_class = 'wpaudit-status_error';
                break;
            case 'success':
                $message_class = 'wpaudit-status_genuine';
                break;
        }
        ?>
        <div class="wpaudit-plugin-check_status <?php echo esc_attr($message_class); ?>">
            <div class="wpaudit-plugin-status_text">
                <?php
                echo $message;
                ?>
            </div>
            <div class="wpaudit-plugin-check-ic wpaudit-status-ic">
                <div></div>
            </div>
        </div>
        <?php
    }

    public function verify_hash($plugin_slug, $plugin_version)
    {
        $plugins_list = wpaudit_supported_plugins();

        $plugin_info = $plugins_list[strtolower($plugin_slug)];
        if (!isset($plugin_info)) {
            $this->show_error(esc_attr__('Plugin not supported!', 'wpaudit'));
        }

        $plugin_slug = explode("/", $plugin_slug, 2);
        $plugin_dir_name = $plugin_slug[0];

        $base_url = Settings::hash_base_url();
        $original_hash_url = $base_url . '/plugins/' . $plugin_info['slug'] . '/' . $plugin_version . '/sha1.json';

        $response = wp_remote_request($original_hash_url);

        if ($response instanceof WP_Error) {
            $this->show_error($response->get_error_message());
        }

        $response_object = $response['http_response']->get_response_object();
        $status_code = $response_object->status_code;

        if ($status_code != 200) {
            if ($status_code == 404) {
                $error = esc_attr__('It seems we do not have this version of plugin in our record or the plugin is using incorrect version to avoid getting caught!', 'wpaudit');
            } else {
                $error = esc_attr__('Error occurred while fetching plugins list, Status Code:%s', 'wpaudit');
                $error = sprintf($error, $status_code);
            }
            $this->show_error($error);
        }

        $original_hash = json_decode($response_object->body, JSON_OBJECT_AS_ARRAY);

        if (empty($original_hash)) {
            $this->show_error(esc_attr__('Error, cannot verify the plugin, please try again after some time!', 'wpaudit'));
        }

        $installed_plugin_hash = HashGenerator::create_plugin_hash($plugin_dir_name);

        if ($this->are_hash_equal($original_hash, $installed_plugin_hash)) {
            $this->show_genuine_message();
        } else {
            $link = $plugin_info['support'];
            if (empty($link)) {
                $link = $plugin_info['AuthorURI'];
            }

            if (is_email($link)) {
                $link = 'mailto:' . $link;
            }
            $text = sprintf('Click %shere%s to contact plugin support', '<a href="' . esc_attr($link) . '" target="_blank">', '</a>');
            $text .= '<br />';
            $text .= esc_attr(__('The plugin looks risky', 'wpaudit'));
            $this->show_error($text);
        }
    }

    public function are_hash_equal($original_hash, $installed_plugin_hash)
    {
        if (sizeof($original_hash) != sizeof($installed_plugin_hash)) {
        //    return false;
        }
        $difference1 = array_diff_assoc($original_hash, $installed_plugin_hash);
        $difference2 = array_diff_assoc($installed_plugin_hash, $original_hash);
        return !(!empty($difference1) || !empty($difference2));
    }

    public function show_genuine_message()
    {
        $message = esc_attr(__('The plugin is Genuine', 'wpaudit'));
        $this->show_message('success', $message);
    }
}