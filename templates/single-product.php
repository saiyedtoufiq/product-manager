<?php
if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
    <?php echo do_blocks('<!-- wp:template-part {"slug":"header","tagName":"header"} /-->'); ?>
<?php
} else {
    get_header();
}

while (have_posts()) :
    the_post();

    $price = get_post_meta(get_the_ID(), '_product_price', true);
    $rating = (int) get_post_meta(get_the_ID(), '_product_rating', true);
    $stock_status = get_post_meta(get_the_ID(), '_product_stock_status', true);
    $rating = max(0, min(5, $rating));
    $is_out_of_stock = ($stock_status === 'out_of_stock');
    $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'large') : PM_PLUGIN_URL . 'assets/images/placeholder.jpg';
?>
    <main class="container py-5">
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

                <div class="pm-detail-cart-controls">
                    <div class="pm-qty-selector" aria-label="<?php esc_attr_e('Quantity selector', 'wp-product-manager'); ?>">
                        <button type="button" class="pm-qty-btn" data-qty-action="minus">-</button>
                        <input type="number" class="pm-qty-input" min="1" value="1" />
                        <button type="button" class="pm-qty-btn" data-qty-action="plus">+</button>
                    </div>

                    <button
                        type="button"
                        class="btn btn-dark rounded-0 py-2 px-4 text-uppercase fw-bold add-to-cart-btn btn-add-main"
                        data-product_id="<?php echo esc_attr(get_the_ID()); ?>"
                        data-qty-source=".pm-qty-input"
                        <?php disabled($is_out_of_stock); ?>>
                        <?php esc_html_e('Add to Cart', 'wp-product-manager'); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h2 class="h5 mb-3"><?php esc_html_e('Description', 'wp-product-manager'); ?></h2>
                <div class="product-content">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </main>
<?php
endwhile;

if (function_exists('wp_is_block_theme') && wp_is_block_theme()) {
?>
    <?php echo do_blocks('<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->'); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
<?php
} else {
    get_footer();
}
