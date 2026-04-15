<?php

class PM_Product_List
{

    public static function init()
    {
        add_shortcode('mp_product_list', array(__CLASS__, 'render_product_list'));
        add_filter('single_template', [__CLASS__, 'product_detail_template']);
    }

    public static function render_product_list($atts)
    {
        ob_start();
?>
        <div class="container product-list">
            <div class="row g-5">
                <!-- Sidebar -->
                <aside class="col-lg-3">
                    <h2 class="filter-section-title">Filtering By</h2>

                    <div class="mb-5">
                        <span class="filter-label">Price:</span>
                        <div id="price-range" class="mb-3 mx-1"></div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem; font-weight: 700;">
                            <span id="price-min" data-min_price="10">₹10</span>
                            <span id="price-max" data-max_price="100000">₹100000</span>
                        </div>
                    </div>

                    <div class="mb-5">
                        <span class="filter-label">Rating:</span>
                        <div id="rating-filters">
                            <div class="rating-container mb-3 cursor-pointer" data-stars="5">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase;">
                                    <span>5 Stars</span>
                                </div>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: 85%"></div>
                                </div>
                            </div>
                            <div class="rating-container mb-3 cursor-pointer" data-stars="4">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase;">
                                    <span>4 Stars</span>
                                </div>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: 60%"></div>
                                </div>
                            </div>
                            <div class="rating-container mb-3 cursor-pointer" data-stars="3">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase;">
                                    <span>3 Stars</span>
                                </div>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: 40%"></div>
                                </div>
                            </div>
                            <div class="rating-container mb-3 cursor-pointer" data-stars="2">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase;">
                                    <span>2 Stars</span>
                                </div>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: 20%"></div>
                                </div>
                            </div>
                            <div class="rating-container mb-3 cursor-pointer" data-stars="1">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase;">
                                    <span>1 Stars</span>
                                </div>
                                <div class="rating-bar-bg">
                                    <div class="rating-bar-fill" style="width: 10%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <span class="filter-label">Stock:</span>
                        <div class="form-check mb-2">
                            <input class="form-check-input rounded-0 border-dark" type="checkbox" id="filter-instock" checked>
                            <label class="form-check-label small text-uppercase fw-bold" style="font-size: 0.65rem;" for="filter-instock">In Stock</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input rounded-0 border-dark" type="checkbox" id="filter-outstock" checked>
                            <label class="form-check-label small text-uppercase fw-bold" style="font-size: 0.65rem;" for="filter-outstock">Out of Stock</label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button id="apply-filters" class="btn btn-dark rounded-0 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">Filter</button>
                        <button id="reset-filters" class="btn btn-outline-dark rounded-0 py-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.1em;">Reset Filter</button>
                    </div>
                </aside>

                <!-- Products Content -->
                <div class="col-lg-9">
                    <div class="d-md-flex justify-content-between align-items-end mb-5 border-bottom pb-3">
                        <h2 class="display-6 fw-light mb-md-0">Products</h2>
                        <div class="search-container col-md-5 col-12">
                            <input type="text" id="search-input" placeholder="product name" class="search-input">
                            <button id="search-btn" class="btn-search">Search</button>
                        </div>
                    </div>

                    <div id="product-grid" class="row">
                        <!-- Products dynamically rendered -->
                    </div>

                    <!-- Pagination -->
                    <div id="post-pagination" class="mt-5 text-center">
                    </div>
                </div>

            </div>
        </div>
<?php
        return ob_get_clean();
    }

    public static function product_detail_template($template) {
        if (!is_singular('product')) {
            return $template;
        }

        $theme_template = locate_template('single-product.php');
        if (!empty($theme_template)) {
            return $theme_template;
        }

        $plugin_template = PM_PLUGIN_DIR . 'templates/single-product.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return $template;
    }

    public static function render_single_product_content($content) {
        return $content;
    }
}
