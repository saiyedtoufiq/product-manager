
jQuery(function($) {

    const state = {
        paged:       1,
        keyword:     '',
        minPrice:    10,
        maxPrice:    100000,
        ratings:     0,
        inStock:     true,
        outOfStock:  true,
        isLoading:   false,
        // cartCount:   parseInt(wpprod_vars.cart_count) || 0,
    };

    window.Swal = Swal;
    window.Toast = Swal.mixin({
        toast: true,
        position: 'bottom',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })
    const $grid       = $('#product-grid');
    const $pagination = $('#post-pagination');

    $("#price-range").slider({
        range: true,
        min: state.minPrice,
        max: state.maxPrice,
        values: [state.minPrice, state.maxPrice],
        slide: function(event, ui) {
            $("#price-min").text("₹" + ui.values[0]).attr("data-min_price", ui.values[0]);
            $("#price-max").text("₹" + ui.values[1]).attr("data-max_price", ui.values[1]);
        }
    });

    $('#apply-filters, #search-btn').on('click', function(e) {
        state.paged = 1;
        switch ($(this).attr('id')) {
            case 'search-btn':
                state.keyword = $('#search-input').val();
                break;
            case 'apply-filters':
                state.minPrice = $("#price-range").slider("values", 0);
                state.maxPrice = $("#price-range").slider("values", 1);
                state.inStock = $('#filter-instock').is(':checked');
                state.outOfStock = $('#filter-outstock').is(':checked');
                break;
        }
        fetchProducts();
    });

    // Rating bar selection
    $(".rating-container").on("click", function() {
        $(".rating-container").removeClass("active");
        $(this).addClass("active");
        state.paged = 1;
        state.ratings = parseInt($(this).data("stars"));
    });

    $('#reset-filters').on('click', function() {
        state.paged = 1;
        $("#price-range").slider("values", [10, 100000]);
        $("#price-min").text("₹10");
        $("#price-max").text("₹100000");
        $(".rating-container").removeClass("active");
        $('#filter-instock, #filter-outstock').prop('checked', true);
        $('#search-input').val('');
        state.ratings = 0;
        state.inStock = true;
        state.outOfStock = true;
        fetchProducts();
    });

    // Pagination
    $(document).on('click', '.page-btn', function () {
        if ($(this).hasClass('active') || $(this).hasClass('text-muted')) return;
        const page = parseInt($(this).data('page'));
        if (page) {
            state.paged = page;
            fetchProducts();
            // Scroll to grid top
            $('html, body').animate({ scrollTop: $grid.offset().top - 80 }, 300);
        }
    });


    // Fetch Products
    function fetchProducts() {
        const data = {
            action: "filter_products",
            nonce: wp_ajax_object.nonce,
            paged: state.paged,
            keyword: state.keyword,
            minPrice: state.minPrice,
            maxPrice: state.maxPrice,
            ratings: state.ratings,
            inStock: state.inStock,
            outOfStock: state.outOfStock,
        };

        $.ajax({
            type: "POST",
            url: wp_ajax_object.ajax_url,
            data: data,
            dataType: "json",
            success: function (response) {
                $grid.css({ opacity: 0, transform: 'translateY(8px)', transition: 'none' });
                $grid.html(response.html);

                setTimeout(function () {
                    $grid.css({ transition: 'opacity .3s ease, transform .3s ease', opacity: 1, transform: 'translateY(0)' });
                }, 20);

                renderPagination(response.current_page, response.total_pages);
            },
        });
    }


    function renderPagination(current, total) {
        if (total <= 1) {
            $pagination.html('');
            return;
        }
        
        let html = `<div class="custom-pagination">
            <button class="page-btn ${current === 1 ? 'text-muted' : ''}" data-page="${current - 1}" >Prev</button>`;
        for (let i = 1; i <= total; i++) {
            html += `<button class="page-btn ${i === current ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        html += `<button class="page-btn ${current === total ? 'text-muted' : ''}" data-page="${current + 1}">Next</button></div>`;
        $pagination.html(html);
    }

    fetchProducts();

    $(document).on('click', '.add-to-cart-btn', function() {
        const productId = $(this).data('product_id');
        const $btn = $(this);

        $.ajax({
            type: "POST",
            url: wp_ajax_object.ajax_url,
            data: {
                action: "add_to_cart",
                nonce: wp_ajax_object.nonce,
                product_id: productId,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message,
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.data.message,
                    });
                }
            },
        });
    });

    $(document).on('click', '.remove-cart-item', function() {
        const productId = $(this).data('product_id');
        $.ajax({
            type: "POST",
            url: wp_ajax_object.ajax_url,
            data: {
                action: "remove_from_cart",
                nonce: wp_ajax_object.nonce,
                product_id: productId,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.data.message,
                    });
                }
            }
        });

    });
});