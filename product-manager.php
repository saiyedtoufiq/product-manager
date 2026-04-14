<?php

/**
 * Plugin Name: Product Manager
 * Description: product management plugin for WordPress
 * Version: 1.0.0
 * Author: Toufiq rehman sayyed
 * Text Domain: wp-product-manager
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

define('PM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PM_PLUGIN_URL', plugin_dir_url(__FILE__));


class Product_Manager {

    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }

    private function load_dependencies() {
        require_once PM_PLUGIN_DIR . 'includes/post-type-init.php';
    }

    private function init_hooks() {
        add_action('init', array($this, 'init'));
    }

    public function init() {
        PM_Product::register();
    }
}

// Initialize the plugin
Product_Manager::get_instance();