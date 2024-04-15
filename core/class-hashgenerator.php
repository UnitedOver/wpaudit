<?php

namespace wpaudit\core;

defined('ABSPATH') || exit;

class HashGenerator
{
    public static function create_plugin_hash($plugin_slug)
    {
        $wp_plugins_dir = self::get_wp_plugin_dir();
        $plugin_dir = $wp_plugins_dir . $plugin_slug;
        if (!is_dir($plugin_dir)) {
            return new \WP_Error('not-found', __('Plugin not found!'));
        }
        $hash = HashGenerator::getFilesSHA1($plugin_dir, '');
        return $hash;
    }

    public static function get_wp_plugin_dir()
    {
        return WP_PLUGIN_DIR . '/';
    }

    public static function getFilesSHA1($dir, $base_dir)
    {
        $files = [];
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $dir . '/' . $file;
                        if (is_dir($filePath)) {
                            $files = array_merge($files, self::getFilesSHA1($filePath, $base_dir . $file . '/'));
                        } else {
                            $files[$base_dir . $file] = sha1_file($filePath);
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $files;
    }

}