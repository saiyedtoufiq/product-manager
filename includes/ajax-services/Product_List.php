<?php

class Product_List {
    public static function filter_products() {
        global $wpdb;
        check_ajax_referer('wp_ajax_nonce', 'nonce');

        $per_page = 6;
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $min_price = isset($_POST['minPrice']) ? floatval($_POST['minPrice']) : 10;
        $max_price = isset($_POST['maxPrice']) ? floatval($_POST['maxPrice']) : 100000;
        $ratings = [];
        if (isset($_POST['ratings'])) {
            $raw_ratings = $_POST['ratings'];
            if (!is_array($raw_ratings)) {
                $raw_ratings = explode(',', (string) $raw_ratings);
            }
            $ratings = array_values(array_filter(array_map('intval', $raw_ratings), function ($value) {
                return $value >= 1 && $value <= 5;
            }));
        }
        $in_stock = isset($_POST['inStock']) ? filter_var($_POST['inStock'], FILTER_VALIDATE_BOOLEAN) : true;
        $out_of_stock = isset($_POST['outOfStock']) ? filter_var($_POST['outOfStock'], FILTER_VALIDATE_BOOLEAN) : true;
        
        $meta_query = array('relation' => 'AND');

        if ($min_price && $max_price) {
            $meta_query[] = array(
                'key' => '_product_price',
                'value' => array($min_price, $max_price),
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            );
        }

        if (!empty($ratings)) {
            $meta_query[] = array(
                'key' => '_product_rating',
                'value' => $ratings,
                'type' => 'NUMERIC',
                'compare' => 'IN'
            );
        }
        if ($in_stock || $out_of_stock) {
            $stock_statuses = [];
            
            if ($in_stock) {
                $stock_statuses[] = 'in_stock';
            }
            if ($out_of_stock) {
                $stock_statuses[] = 'out_of_stock';
            }
            
            $meta_query[] = [
                'key'     => '_product_stock_status',
                'value'   => $stock_statuses,
                'compare' => 'IN'
            ];
        }

        // --- Base query args ---
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'meta_query'     => $meta_query,
        ];

        if (!empty($keyword)) {
            $search_term = '%' . $wpdb->esc_like($keyword) . '%';
            add_filter('posts_join', function($join) {
                global $wpdb;
                $join .= " LEFT JOIN {$wpdb->postmeta} AS pm1 ON ({$wpdb->posts}.ID = pm1.post_id AND pm1.meta_key = '_product_price')";
                return $join;
            });
            add_filter('posts_where', function($where) use ($search_term) {
                global $wpdb;
                $where .= $wpdb->prepare(
                    " AND (
                        {$wpdb->posts}.post_title LIKE %s 
                        OR {$wpdb->posts}.post_content LIKE %s 
                        OR ( pm1.meta_key IN ('_product_price' , '_product_rating', '_product_stock_status') AND pm1.meta_value LIKE %s )
                    )", $search_term, $search_term, $search_term);
                return $where;
            });
        }

        $query = new WP_Query($args);

        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                include PM_PLUGIN_DIR . 'templates/product-card.php';
            }
            wp_reset_postdata();
        } else {
            echo '<div class="col-12"><p class="text-center">No products found.</p></div>';
        }

        $html = ob_get_clean();

        wp_send_json([
            'html' => $html,
            'current_page' => $paged,
            'total_pages' => $query->max_num_pages
        ]);
    }
}