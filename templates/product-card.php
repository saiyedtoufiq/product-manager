<?php
    if ( ! defined( 'ABSPATH' ) ) exit;

    $isStock = get_post_meta(get_the_ID(), '_product_stock_status', true);
    $price = get_post_meta(get_the_ID(), '_product_price', true);
    $rating = get_post_meta(get_the_ID(), '_product_rating', true);
?>
<div class="col-md-6 col-lg-4">
    <div class="product-card">
        <div class="product-img-wrapper">
            <?php if ($isStock === 'out_of_stock'): ?>
                <div class="out-of-stock-tag">Out of Stock</div>
            <?php endif; ?>
            <?php if (has_post_thumbnail()): ?>
                <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title(); ?>" class="product-img">
            <?php else: ?>
                <img src="<?php echo PM_PLUGIN_URL . 'assets/images/placeholder.jpg'; ?>" alt="no image" class="product-img">
            <?php endif; ?>
            <div class="add-to-cart-overlay">
                <button class="btn btn-dark rounded-0 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">Add to Cart</button>
            </div>
        </div>
        <div class="text-center">
            <h5 class="small fw-bold text-uppercase mb-1 lowercase"><?php the_title(); ?></h5>
            <p class="text-muted italic small mb-2">₹<?php echo number_format($price, 2); ?></p>
            <div class="text-secondary" style="font-size: 0.6rem;">
                <?php echo str_repeat('★', $rating) . str_repeat('☆', 5 - $rating); ?>
            </div>
        </div>
    </div>
</div>