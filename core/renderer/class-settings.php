<?php

namespace WPAudit\core\renderer;

if (!defined('ABSPATH')) {
    exit;
}


class Settings
{
    const ACTION = 'wpaudit_verify_plugin_hash';
    protected static $_instance = null;

    /**
     *  Constructor.
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function render()
    {

        $available_plugins = get_plugins();

        $plugins_list = wpaudit_supported_plugins();
        $nonce = wp_create_nonce(self::ACTION);

        ?>
        <div class="wpaudit_container" data-nonce="<?php echo esc_attr($nonce); ?>">
            <div class="wpaudit_title">
                <?php esc_attr_e('Make sure the plugins you are using are genuine', 'wpaudit'); ?>
            </div>
            <div class="wpaudit_plugins-list_box">
                <?php
                if (empty($plugins_list)) {
                    echo '<div class="wpaudit-no-supported-plugins">' . esc_attr__('Oops, No Supported Plugins found!', 'wpaudit') . '</div>';
                } else {
                    foreach ($available_plugins as $o_plugin_slug => $plugin) {
                        $plugin_slug = strtolower($o_plugin_slug);
                        if (!isset($plugins_list[$plugin_slug])) {
                            continue;
                        }
                        $plugin_info = $plugins_list[$plugin_slug];
                        $author = $plugin['Author'];
                        $author_uri = $plugin['AuthorURI'];
                        $plugin_version = $plugin['Version']
                        ?>
                        <div class="wpaudit_plugin_info">
                            <?php
                            $plugin_logo = $plugin_info['icon'];
                            ?>
                            <div class="wpaudit_plugin_info_wrapper">
                                <div>
                                    <img class="wpaudit_plugin_info_logo" src="<?php echo esc_attr($plugin_logo); ?>"
                                         draggable="false" alt="<?php echo esc_attr(__('Plugin Logo', 'wpaudit')); ?>"/>
                                </div>
                                <div class="wpaudit_plugin_details">
                                    <div class="wpaudit_plugin_basic_info">
                                        <div class="wpaudit_plugin_info-name">
                                            <a href="<?php echo esc_attr($plugin['PluginURI']); ?>" target="_blank">
                                                <?php echo esc_attr($plugin['Name']); ?>
                                            </a>
                                        </div>
                                        <div class="wpaudit_plugin_info-version">
                                            v&nbsp;<?php echo esc_attr($plugin_version); ?>
                                        </div>
                                    </div>
                                    <div class="wpaudit_plugin_info-author">
                                        <a href="<?php echo esc_attr($author_uri); ?>" target="_blank">
                                            <?php echo esc_attr(__('By', 'wpaudit') . '  ' . $author); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="wpaudit_plugin_info-action">
                                    <div class="wpaudit_check-plugin-btn"
                                         data-version="<?php echo esc_attr($plugin_version); ?>"
                                         data-plugin="<?php echo esc_attr($o_plugin_slug); ?>">
                                        <?php echo esc_attr(__('Check', 'wpaudit')); ?>
                                    </div>
                                    <?php
                                    $this->processing_html();
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="footer-wpaudit_logo">
                <img class="wpaudit-logo" src="<?php echo esc_attr(self::get_logo_url()); ?>" alt="WPAudit Logo"
                     draggable="false"/>
            </div>
        </div>
        <?php
    }

    public function processing_html()
    {
        ?>
        <div class="wpaudit-plugin-check_status processing hide">
            <div class="wpaudit-plugin-status_text">
                <?php echo esc_attr(__('processing', 'wpaudit')); ?>
            </div>
            <div class="wpaudit-plugin-check-ic wpaudit-status-ic">
                <div></div>
            </div>
        </div>
        <?php
    }

    public static function get_logo_url()
    {
        return get_wpaudit_asset_uri('/assets/svg/wpaudit.svg');
    }

    public static function hash_base_url()
    {
        return 'https://raw.githubusercontent.com/UnitedOver/wpaudit/main/hash/';
    }

}