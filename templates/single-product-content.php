<?php
if (!defined('ABSPATH')) {
    exit;
}

$price = get_post_meta(get_the_ID(), '_product_price', true);
$rating = (int) get_post_meta(get_the_ID(), '_product_rating', true);
$stock_status = get_post_meta(get_the_ID(), '_product_stock_status', true);
$rating = max(0, min(5, $rating));
$is_out_of_stock = ($stock_status === 'out_of_stock');
$image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : PM_PLUGIN_URL . 'assets/images/placeholder.jpg';
?>
<div class="container py-5">
    <div class="row g-5 align-items-start">
        <div class="col-lg-6">
            <div class="product-img-wrapper mb-3">
                <?php if ($is_out_of_stock) : ?>
                    <div class="out-of-stock-tag"><?php esc_html_e('Out of Stock', 'wp-product-manager'); ?></div>
                <?php endif; ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php the_title_attribute(); ?>" class="product-img" />
            </div>
        </div>

        <div class="col-lg-6">
            <h1 class="product-title"><?php the_title(); ?></h1>
            <div class="text-secondary mb-3" style="font-size: 0.9rem;">
                <?php echo esc_html(str_repeat('★', $rating) . str_repeat('☆', 5 - $rating)); ?>
            </div>

            <p class="product-price mb-3">
                <?php
                if ($price !== '') {
                    echo esc_html('₹' . number_format((float) $price, 2));
                } else {
                    esc_html_e('Price on request', 'wp-product-manager');
                }
                ?>
            </p>

            <p class="product-meta mb-4">
                <strong><?php esc_html_e('Stock:', 'wp-product-manager'); ?></strong>
                <?php echo $is_out_of_stock ? esc_html__('Out of Stock', 'wp-product-manager') : esc_html__('In Stock', 'wp-product-manager'); ?>
            </p>

            <button
                type="button"
                class="btn btn-dark rounded-0 py-2 px-4 text-uppercase fw-bold add-to-cart-btn"
                data-product_id="<?php echo esc_attr(get_the_ID()); ?>"
                <?php disabled($is_out_of_stock); ?>>
                <?php esc_html_e('Add to Cart', 'wp-product-manager'); ?>
            </button>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h2 class="h5 mb-3"><?php esc_html_e('Description', 'wp-product-manager'); ?></h2>
            <div class="product-content">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</div>
