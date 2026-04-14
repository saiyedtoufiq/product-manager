<?php

class PM_Product {
    public static function register() {
        $labels = array(
            'name' => __('Products', 'wp-product-manager'),
            'singular_name' => __('Product', 'wp-product-manager'),
            'menu_name' => __('Products', 'wp-product-manager'),
            'name_admin_bar' => __('Product', 'wp-product-manager'),
            'add_new' => __('Add New', 'wp-product-manager'),
            'add_new_item' => __('Add New Product', 'wp-product-manager'),
            'new_item' => __('New Product', 'wp-product-manager'),
            'edit_item' => __('Edit Product', 'wp-product-manager'),
            'view_item' => __('View Product', 'wp-product-manager'),
            'all_items' => __('All Products', 'wp-product-manager'),
            'search_items' => __('Search Products', 'wp-product-manager'),
            'parent_item_colon' => __('Parent Products:', 'wp-product-manager'),
            'not_found' => __('No products found.', 'wp-product-manager'),
            'not_found_in_trash' => __('No products found in Trash.', 'wp-product-manager'),
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'products'),
            'supports' => array('title', 'editor', 'thumbnail'),
            'menu_icon' => 'dashicons-cart',
        );

        register_post_type('product', $args);

        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_product', [__CLASS__, 'save_post_pm_product']);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'product_details',
            __('Product Details', 'wp-product-manager'),
            array(__CLASS__, 'render_product_details_meta_box'),
            'product',
            'normal',
            'high'
        );
    }

    public static function render_product_details_meta_box($post) {
        wp_nonce_field('pm_meta_box', 'pm_meta_box_nonce');

        $price = get_post_meta($post->ID, '_product_price', true);
        $rating = get_post_meta($post->ID, '_product_rating', true) ? get_post_meta($post->ID, '_product_rating', true) : 0;
        $stock_status = get_post_meta($post->ID, '_product_stock_status', true) ? get_post_meta($post->ID, '_product_stock_status', true) : 1;
        ?>
            <div class="">
                <p>
                    <label for="product_price"><?php _e('Price', 'wp-product-manager'); ?></label>
                    <input type="text" id="product_price" name="product_price" value="<?php echo esc_attr($price); ?>" placeholder="Product price" />
                </p>
                <p>
                    <label for="product_rating"><?php _e('Rating', 'wp-product-manager'); ?></label>
                    <input type="range" id="product_rating" name="product_rating" min="0" max="5" step="0.1" value="<?php echo esc_attr($rating); ?>" />
                    <span class="rating-value"><?php echo esc_attr($rating); ?></span>
                </p>
                <p>
                    <label for="product_stock_status"><?php _e('Stock Status', 'wp-product-manager'); ?></label>
                    <input type="radio" id="stock_in" name="product_stock_status" value="1" <?php checked($stock_status, '1'); ?> />
                    <label for="stock_in"><?php _e('In Stock', 'wp-product-manager'); ?></label>
                    <input type="radio" id="stock_out" name="product_stock_status" value="0" <?php checked($stock_status, '0'); ?> />
                    <label for="stock_out"><?php _e('Out of Stock', 'wp-product-manager'); ?></label>
                </p>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    $('#product_rating').on('input change', function() {
                        var value = $(this).val();
                        $(this).next('.rating-value').remove();
                        $(this).after('<span class="rating-value">' + value + '</span>');
                    }).trigger('input');
                });
            </script>
        <?php
    }

    public static function save_post_pm_product($post_id) {
        if (!isset($_POST['pm_meta_box_nonce']) || !wp_verify_nonce($_POST['pm_meta_box_nonce'], 'pm_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (isset($_POST['product_price'])) {
            update_post_meta($post_id, '_product_price', sanitize_text_field($_POST['product_price']));
        }

        if (isset($_POST['product_rating'])) {
            update_post_meta($post_id, '_product_rating', sanitize_text_field($_POST['product_rating']));
        }

        if (isset($_POST['product_stock_status'])) {
            update_post_meta($post_id, '_product_stock_status', sanitize_text_field($_POST['product_stock_status']));
        }
    }
}