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
        require_once PM_PLUGIN_DIR . 'includes/shortcodes-init.php';
        require_once PM_PLUGIN_DIR . 'includes/ajax-handler.php';
    }

    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'mp_enqueue_scripts'));
        add_action('wp_ajax_filter_products', ['Product_List', 'filter_products']);
        add_action('wp_ajax_nopriv_filter_products', ['Product_List', 'filter_products']);
    }

    public function init() {
        // post types
        PM_Product::register();

        // shortcodes
        PM_Product_List::init();
    }

    public function mp_enqueue_scripts() {
        // bootstrap css and custom styles
        wp_enqueue_style('pm-bootstrap', "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css");
        wp_enqueue_style('pm-jquery-ui', "https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css");
        wp_enqueue_style('pm-styles', PM_PLUGIN_URL . 'assets/css/styles.css');
        // jquery and custom scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('pm-jquery-ui', "https://code.jquery.com/ui/1.13.2/jquery-ui.js", array('jquery'), null, true);
        wp_enqueue_script('pm-scripts', PM_PLUGIN_URL . 'assets/js/scripts.js', array('jquery'), null, true);
        wp_localize_script('pm-scripts', 'wp_ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_ajax_nonce')
        ]);
    }
}

// Initialize the plugin
Product_Manager::get_instance();