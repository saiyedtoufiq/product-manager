<?php

class PM_Cart {

    public static function init() {
        add_shortcode('pm_cart', [__CLASS__, 'product_cart_shortcode']);
        add_action('wp_footer', [__CLASS__, 'render_cart_drawer_shell']);
    }

    private static function get_session_cart() {
        if ( ! session_id() ) {
            @session_start();
        }
        return isset( $_SESSION['cart_products'] ) ? $_SESSION['cart_products'] : [];
    }

    private static function save_session_cart( $cart ) {
        if ( ! session_id() ) {
            @session_start();
        }
        $_SESSION['cart_products'] = $cart;
    }

    public static function get_items() {
        $cart_items = [];
        $cartSessionItems = self::get_session_cart();
        foreach ($cartSessionItems as $key => $item) {
            $product = get_post($key);
            if ($product && $product->post_status === 'publish') {
                $price = get_post_meta($key, '_product_price', true);
                $cart_items[] = [
                    'id' => $product->ID,
                    'title' => $product->post_title,
                    'price' => $price,
                    'quantity' => $item['qty'],
                    'subtotal' => $price * $item['qty'],
                    'thumbnail' => get_the_post_thumbnail_url($key, 'thumbnail')
                ];
            }
        }
        
        return $cart_items;
    }

    public static function get_count() {
        $cart = self::get_session_cart();
        return array_sum(array_column($cart, 'qty'));
    }

    public static function get_totals() {
        $items = self::get_items();
        $subtotal = array_sum(array_column($items, 'subtotal'));

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'count' => self::get_count(),
        ];
    }

    public static function get_cart() {
        check_ajax_referer('wp_ajax_nonce', 'nonce');
        $totals = self::get_totals();
        $totals['html'] = self::render_cart_drawer_items_html($totals['items'], $totals['subtotal'], $totals['total']);
        wp_send_json_success($totals);
    }

    public static function add_to_cart() {
        check_ajax_referer('wp_ajax_nonce', 'nonce');
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        $qty = max(1, intval($_POST['qty'] ?? 1));
        
        if ( ! $product_id || get_post_type( $product_id ) !== 'product' ) {
            wp_send_json_error( [ 'message' => __( 'Invalid product.', 'wp-product-manager' ) ] );
        }
        
        $stock = get_post_meta( $product_id, '_product_stock_status', true );
        if ( $stock !== 'in_stock' ) {
            wp_send_json_error( [ 'message' => __( 'Product is out of stock.', 'wp-product-manager' ) ] );
        }

        $cart = self::get_session_cart();

        if ( isset( $cart[ $product_id ] ) ) {
            $cart[ $product_id ]['qty'] += $qty;
        } else {
            $cart[ $product_id ] = [
                'qty'   => $qty,
                'title' => get_the_title( $product_id ),
                'price' => get_post_meta( $product_id, '_product_price', true ),
                'thumb' => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
            ];
        }

        self::save_session_cart( $cart );
        $totals = self::get_totals();
        
        wp_send_json_success([
            'message' => 'Product added to cart',
            'cart_count' => $totals['count'],
            'subtotal' => $totals['subtotal'],
            'total' => $totals['total'],
            'html' => self::render_cart_drawer_items_html($totals['items'], $totals['subtotal'], $totals['total']),
        ]);
    }

    public static function remove_from_cart() {
        check_ajax_referer( 'wp_ajax_nonce', 'nonce' );
        $product_id = intval( $_POST['product_id'] ?? 0 );
        $cart       = self::get_session_cart();
        unset( $cart[ $product_id ] );
        self::save_session_cart( $cart );
        $totals = self::get_totals();
        wp_send_json_success( [
            'message'    => 'Product removed from cart',
            'cart_count' => $totals['count'],
            'subtotal' => $totals['subtotal'],
            'total' => $totals['total'],
            'html' => self::render_cart_drawer_items_html($totals['items'], $totals['subtotal'], $totals['total']),
        ] );
    }

    private static function render_cart_drawer_items_html($items, $subtotal, $total) {
        ob_start();
        ?>
        <div class="pm-cart-drawer-header">
            <h3><?php esc_html_e('Your Cart', 'wp-product-manager'); ?></h3>
            <button type="button" class="pm-cart-close" aria-label="<?php esc_attr_e('Close cart', 'wp-product-manager'); ?>">×</button>
        </div>
        <div class="pm-cart-drawer-items">
            <?php if (empty($items)) : ?>
                <p class="pm-cart-empty"><?php esc_html_e('Your cart is empty.', 'wp-product-manager'); ?></p>
            <?php else : ?>
                <?php foreach ($items as $item) : ?>
                    <div class="pm-cart-drawer-item" id="pm-drawer-item-<?php echo esc_attr($item['id']); ?>">
                        <img
                            src="<?php echo esc_url(!empty($item['thumbnail']) ? $item['thumbnail'] : PM_PLUGIN_URL . 'assets/images/placeholder.jpg'); ?>"
                            alt="<?php echo esc_attr($item['title']); ?>"
                            class="pm-cart-item-thumb"
                        />
                        <div class="pm-cart-item-info">
                            <p class="pm-cart-item-title"><?php echo esc_html($item['title']); ?></p>
                            <p class="pm-cart-item-meta">
                                <?php echo esc_html($item['quantity']); ?> × ₹<?php echo esc_html(number_format((float) $item['price'], 2)); ?>
                            </p>
                        </div>
                        <div class="pm-cart-item-actions">
                            <span class="pm-cart-item-subtotal">₹<?php echo esc_html(number_format((float) $item['subtotal'], 2)); ?></span>
                            <button type="button" class="remove-cart-item" data-product_id="<?php echo esc_attr($item['id']); ?>">
                                <?php esc_html_e('Remove', 'wp-product-manager'); ?>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="pm-cart-drawer-footer">
            <div class="pm-cart-row">
                <span><?php esc_html_e('Subtotal', 'wp-product-manager'); ?></span>
                <strong>₹<?php echo esc_html(number_format((float) $subtotal, 2)); ?></strong>
            </div>
            <div class="pm-cart-row">
                <span><?php esc_html_e('Total', 'wp-product-manager'); ?></span>
                <strong>₹<?php echo esc_html(number_format((float) $total, 2)); ?></strong>
            </div>
            <a href="<?php echo esc_url(home_url('/cart')); ?>" class="pm-cart-go-to-cart">
                <?php esc_html_e('Go to Cart', 'wp-product-manager'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_cart_drawer_shell() {
        if (is_admin()) {
            return;
        }
        ?>
        <div class="pm-cart-ui">
            <button type="button" class="pm-cart-toggle" aria-label="<?php esc_attr_e('Open cart', 'wp-product-manager'); ?>">
                <span class="pm-cart-toggle-label"><?php esc_html_e('Cart', 'wp-product-manager'); ?></span>
                <span class="pm-cart-count"><?php echo esc_html(self::get_count()); ?></span>
            </button>

            <div class="pm-cart-backdrop"></div>
            <aside class="pm-cart-drawer" aria-hidden="true">
                <?php
                $totals = self::get_totals();
                echo self::render_cart_drawer_items_html($totals['items'], $totals['subtotal'], $totals['total']);
                ?>
            </aside>
        </div>
        <?php
    }

    public static function product_cart_shortcode() {
        $cartItems = self::get_items();
        $grandTotal = array_sum( array_column( $cartItems, 'subtotal' ) );
        ob_start();
        ?>
            <div class="container px-4 py-5 pm-cart-page" id="pm-cart-page">
                <div class="cart-header">
                    <h1 class="display-6 fw-light">Your Cart</h1>
                </div>
                <div class="row g-5">
                    <div class="col-lg-8">
                        <div id="cart-items-container">
                            <?php if (empty($cartItems)) : ?>
                                <p class="pm-cart-empty">Your cart is empty.</p>
                            <?php else : ?>
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="pm-cart-drawer-item pm-cart-page-item" id="cart-item-<?php echo esc_attr( $item['id'] ); ?>">
                                        <img
                                            src="<?php echo esc_url(!empty($item['thumbnail']) ? $item['thumbnail'] : PM_PLUGIN_URL . 'assets/images/placeholder.jpg'); ?>"
                                            alt="<?php echo esc_attr($item['title']); ?>"
                                            class="pm-cart-item-thumb"
                                        />
                                        <div class="pm-cart-item-info">
                                            <p class="pm-cart-item-title"><?php echo esc_html( $item['title'] ); ?></p>
                                            <p class="pm-cart-item-meta">
                                                <?php echo esc_html( $item['quantity'] ); ?> × ₹<?php echo number_format( (float) $item['price'], 2 ); ?>
                                            </p>
                                        </div>
                                        <div class="pm-cart-item-actions">
                                            <span class="pm-cart-item-subtotal">₹<?php echo number_format( (float) $item['subtotal'], 2 ); ?></span>
                                            <button class="remove-cart-item" data-product_id="<?php echo esc_attr( $item['id'] ); ?>">Remove</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="mt-5 d-flex justify-content-between align-items-center">
                            <a href="<?php echo home_url('shop'); ?>" class="text-dark text-decoration-none small fw-bold text-uppercase tracking-wider">← Back to Shop</a>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h2 class="filter-section-title mb-4" style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #000; padding-bottom: 0.5rem;">Order Summary</h2>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span class="summary-label">Subtotal</span>
                                <span class="summary-value">₹<?php echo number_format( (float) $grandTotal, 2 ); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-4 pt-3 border-top">
                                <span class="summary-label">Total</span>
                                <span class="summary-value fs-5">₹<?php echo number_format( (float) $grandTotal, 2 ); ?></span>
                            </div>

                            <button class="btn-checkout">Proceed to Checkout</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        return ob_get_clean();
    }
}