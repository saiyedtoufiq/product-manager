<?php

class PM_Cart {

    public static function init() {
        add_shortcode('pm_cart', [__CLASS__, 'product_cart_shortcode']);
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

    // public function remove_item($product_id) {
    //     if (isset($this->items[$product_id])) {
    //         unset($this->items[$product_id]);
    //         $this->save();
    //     }
    // }

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

    public static function add_to_cart() {
        check_ajax_referer('wp_ajax_nonce', 'nonce');
        
        $product_id = intval( $_POST['product_id'] ?? 0 );
        
        if ( ! $product_id || get_post_type( $product_id ) !== 'product' ) {
            wp_send_json_error( [ 'message' => __( 'Invalid product.', 'wp-product-manager' ) ] );
        }
        
        $stock = get_post_meta( $product_id, '_product_stock_status', true );
        if ( $stock !== 'in_stock' ) {
            wp_send_json_error( [ 'message' => __( 'Product is out of stock.', 'wp-product-manager' ) ] );
        }

        $cart = self::get_session_cart();

        if ( isset( $cart[ $product_id ] ) ) {
            $cart[ $product_id ]['qty']++;
        } else {
            $cart[ $product_id ] = [
                'qty'   => 1,
                'title' => get_the_title( $product_id ),
                'price' => get_post_meta( $product_id, '_product_price', true ),
                'thumb' => get_the_post_thumbnail_url( $product_id, 'thumbnail' ),
            ];
        }

        self::save_session_cart( $cart );
        
        wp_send_json_success([
            'message' => 'Product added to cart',
            'cart_count' => array_sum( array_column( $cart, 'qty' ) )
        ]);
    }

    public static function remove_from_cart() {
        check_ajax_referer( 'wp_ajax_nonce', 'nonce' );
        $product_id = intval( $_POST['product_id'] ?? 0 );
        $cart       = self::get_session_cart();
        unset( $cart[ $product_id ] );
        self::save_session_cart( $cart );
        wp_send_json_success( [
            'message'    => 'Product removed from cart',
            'cart_count' => array_sum( array_column( $cart, 'qty' ) ),
        ] );
    }

    public static function product_cart_shortcode() {
        $cartItems = self::get_items();
        $grandTotal = array_sum( array_column( $cartItems, 'subtotal' ) );
        ob_start();
        ?>
            <div class="container px-4 py-5">
                <div class="cart-header">
                    <h1 class="display-6 fw-light">Your Cart</h1>
                </div>
                <div class="row g-5">
                    <div class="col-lg-8">
                        <div id="cart-items-container">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item" id="cart-item-<?php echo esc_attr( $item['id'] ); ?>">
                                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200" alt="Product" class="product-thumb me-4">
                                    <div class="product-info flex-grow-1">
                                        <h3 class="lowercase"><?php echo esc_html( $item['title'] ); ?></h3>
                                        <p>$<?php echo number_format( $item['price'], 2 ); ?></p>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span><?php echo esc_html( $item['quantity'] ); ?></span>
                                        <span class="summary-value ms-4">$<?php echo number_format( $item['subtotal'], 2 ); ?></span>
                                        <button class="remove-btn ms-3 remove-cart-item" data-product_id="<?php echo esc_attr( $item['id'] ); ?>">X</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
                                <span class="summary-value">$<?php echo number_format( $grandTotal, 2 ); ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-4 pt-3 border-top">
                                <span class="summary-label">Total</span>
                                <span class="summary-value fs-5">$<?php echo number_format( $grandTotal, 2 ); ?></span>
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