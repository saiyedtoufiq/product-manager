
jQuery(function($) {
    const state = {
        paged: 1,
        keyword: '',
        minPrice: 10,
        maxPrice: 100000,
        ratings: [],
        inStock: true,
        outOfStock: true,
    };

    const $grid = $('#product-grid');
    const $pagination = $('#post-pagination');
    const $cartUI = $('.pm-cart-ui');
    const $drawer = $('.pm-cart-drawer');
    const $backdrop = $('.pm-cart-backdrop');
    const $count = $('.pm-cart-count');

    window.Swal = Swal;
    window.Toast = Swal.mixin({
        toast: true,
        position: 'bottom',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    function openDrawer() {
        $cartUI.addClass('is-open');
        $drawer.attr('aria-hidden', 'false');
    }

    function closeDrawer() {
        $cartUI.removeClass('is-open');
        $drawer.attr('aria-hidden', 'true');
    }

    function updateDrawerUI(data) {
        if (typeof data.cart_count !== 'undefined') {
            $count.text(data.cart_count);
        }
        if (data.html) {
            $drawer.html(data.html);
        }
    }

    function fetchCart() {
        $.ajax({
            type: 'POST',
            url: wp_ajax_object.ajax_url,
            dataType: 'json',
            data: {
                action: 'get_cart',
                nonce: wp_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDrawerUI(response.data);
                }
            }
        });
    }

    function addToCart(productId, qty) {
        $.ajax({
            type: 'POST',
            url: wp_ajax_object.ajax_url,
            dataType: 'json',
            data: {
                action: 'add_to_cart',
                nonce: wp_ajax_object.nonce,
                product_id: productId,
                qty: qty
            },
            success: function(response) {
                if (response.success) {
                    updateDrawerUI(response.data);
                    // openDrawer();
                    Toast.fire({ icon: 'success', title: response.data.message });
                } else {
                    Toast.fire({ icon: 'error', title: response.data.message || 'Unable to add product.' });
                }
            }
        });
    }

    function removeFromCart(productId) {
        $.ajax({
            type: 'POST',
            url: wp_ajax_object.ajax_url,
            dataType: 'json',
            data: {
                action: 'remove_from_cart',
                nonce: wp_ajax_object.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    updateDrawerUI(response.data);
                    if ($('#pm-cart-page').length) {
                        window.location.reload();
                        return;
                    }
                    Toast.fire({ icon: 'success', title: response.data.message });
                }
            }
        });
    }

    if ($('#price-range').length) {
        $('#price-range').slider({
            range: true,
            min: state.minPrice,
            max: state.maxPrice,
            values: [state.minPrice, state.maxPrice],
            slide: function(event, ui) {
                $('#price-min').text('₹' + ui.values[0]).attr('data-min_price', ui.values[0]);
                $('#price-max').text('₹' + ui.values[1]).attr('data-max_price', ui.values[1]);
            }
        });
    }

    $('#apply-filters, #search-btn').on('click', function() {
        state.paged = 1;
        if ($(this).attr('id') === 'search-btn') {
            state.keyword = $('#search-input').val();
        } else {
            state.minPrice = $('#price-range').slider('values', 0);
            state.maxPrice = $('#price-range').slider('values', 1);
            state.inStock = $('#filter-instock').is(':checked');
            state.outOfStock = $('#filter-outstock').is(':checked');
        }
        fetchProducts();
    });

    $('#search-form').submit(function(e) {
        e.preventDefault();
        state.paged = 1;
        state.keyword = $('#search-input').val();
        fetchProducts();
    });

    $('.rating-container').on('click', function() {
        const star = parseInt($(this).data('stars'), 10);
        const index = state.ratings.indexOf(star);
        state.paged = 1;

        if (index === -1) {
            state.ratings.push(star);
            $(this).addClass('active');
        } else {
            state.ratings.splice(index, 1);
            $(this).removeClass('active');
        }
    });

    $('#reset-filters').on('click', function() {
        state.paged = 1;
        $('#price-range').slider('values', [10, 100000]);
        $('#price-min').text('₹10');
        $('#price-max').text('₹100000');
        $('.rating-container').removeClass('active');
        $('#filter-instock, #filter-outstock').prop('checked', true);
        $('#search-input').val('');
        state.ratings = [];
        state.inStock = true;
        state.outOfStock = true;
        fetchProducts();
    });

    $(document).on('click', '.page-btn', function() {
        if ($(this).hasClass('active') || $(this).hasClass('text-muted')) {
            return;
        }
        const page = parseInt($(this).data('page'), 10);
        if (page) {
            state.paged = page;
            fetchProducts();
            if ($grid.length) {
                $('html, body').animate({ scrollTop: $grid.offset().top - 80 }, 300);
            }
        }
    });

    function fetchProducts() {
        if (!$grid.length) {
            return;
        }

        const skeletonMinDuration = 300;
        const requestStartedAt = Date.now();
        renderProductSkeletons(6);

        $.ajax({
            type: 'POST',
            url: wp_ajax_object.ajax_url,
            dataType: 'json',
            data: {
                action: 'filter_products',
                nonce: wp_ajax_object.nonce,
                paged: state.paged,
                keyword: state.keyword,
                minPrice: state.minPrice,
                maxPrice: state.maxPrice,
                ratings: state.ratings,
                inStock: state.inStock,
                outOfStock: state.outOfStock
            },
            success: function(response) {
                const elapsed = Date.now() - requestStartedAt;
                const waitFor = Math.max(0, skeletonMinDuration - elapsed);

                setTimeout(function() {
                    $grid.css({ opacity: 0, transform: 'translateY(8px)', transition: 'none' });
                    $grid.html(response.html);
                    setTimeout(function() {
                        $grid.css({ transition: 'opacity .3s ease, transform .3s ease', opacity: 1, transform: 'translateY(0)' });
                    }, 200);
                    renderPagination(response.current_page, response.total_pages);
                }, waitFor);
            }
        });
    }

    function renderProductSkeletons(count) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += ''
                + '<div class="col-md-6 col-lg-4">'
                + '  <div class="product-card pm-skeleton-card">'
                + '    <div class="pm-skeleton pm-skeleton-image"></div>'
                + '    <div class="text-center">'
                + '      <div class="pm-skeleton pm-skeleton-title mx-auto"></div>'
                + '      <div class="pm-skeleton pm-skeleton-price mx-auto"></div>'
                + '      <div class="pm-skeleton pm-skeleton-rating mx-auto"></div>'
                + '    </div>'
                + '  </div>'
                + '</div>';
        }
        $grid.html(html);
        $grid.css({ opacity: 1, transform: 'translateY(0)', transition: 'none' });
    }

    function renderPagination(current, total) {
        if (!$pagination.length || total <= 1) {
            $pagination.html('');
            return;
        }
        let html = '<div class="custom-pagination">';
        html += '<button class="page-btn ' + (current === 1 ? 'text-muted' : '') + '" data-page="' + (current - 1) + '">Prev</button>';
        for (let i = 1; i <= total; i++) {
            html += '<button class="page-btn ' + (i === current ? 'active' : '') + '" data-page="' + i + '">' + i + '</button>';
        }
        html += '<button class="page-btn ' + (current === total ? 'text-muted' : '') + '" data-page="' + (current + 1) + '">Next</button>';
        html += '</div>';
        $pagination.html(html);
    }

    if ($grid.length) {
        fetchProducts();
    }
    fetchCart();

    $(document).on('click', '.add-to-cart-btn', function() {
        const productId = $(this).data('product_id');
        let qty = parseInt($(this).data('qty'), 10) || 1;

        const qtySource = $(this).data('qty-source');
        if (qtySource) {
            const $qtyInput = $(qtySource).first();
            qty = parseInt($qtyInput.val(), 10) || 1;
        }
        qty = Math.max(1, qty);
        addToCart(productId, qty);
    });

    $(document).on('click', '.remove-cart-item', function() {
        const productId = $(this).data('product_id');
        removeFromCart(productId);
    });

    $(document).on('click', '.pm-cart-toggle', function() {
        openDrawer();
    });

    $(document).on('click', '.pm-cart-close, .pm-cart-backdrop', function() {
        closeDrawer();
    });

    $(document).on('click', '.pm-qty-btn', function() {
        const action = $(this).data('qty-action');
        const $input = $(this).siblings('.pm-qty-input');
        const current = parseInt($input.val(), 10) || 1;
        const next = action === 'minus' ? Math.max(1, current - 1) : current + 1;
        $input.val(next);
    });

    $(document).on('change', '.pm-qty-input', function() {
        let value = parseInt($(this).val(), 10) || 1;
        value = Math.max(1, value);
        $(this).val(value);
    });
});