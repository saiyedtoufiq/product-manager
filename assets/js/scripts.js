let selectedMinRating = 0;

const products = [
    { id: 1, title: "product title 01", price: 120.00, rating: 5, inStock: true, img: "https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500" },
    { id: 2, title: "product title 02", price: 250.00, rating: 4, inStock: true, img: "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500" },
    { id: 3, title: "product title 03", price: 450.00, rating: 5, inStock: false, img: "https://images.unsplash.com/photo-1505843490701-5be5d0b19d58?w=500" },
    { id: 4, title: "product title 04", price: 89.99, rating: 3, inStock: true, img: "https://images.unsplash.com/photo-1589492477829-5e65395b66cc?w=500" },
    { id: 5, title: "product title 05", price: 175.00, rating: 4, inStock: true, img: "https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=500" },
    { id: 6, title: "product title 06", price: 140.00, rating: 5, inStock: false, img: "https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=500" }
];

jQuery(function($) {
    $("#price-range").slider({
        range: true,
        min: 10,
        max: 100000,
        values: [10, 100000],
        slide: function(event, ui) {
            $("#price-min").text("₹" + ui.values[0]).attr("data-min_price", ui.values[0]);
            $("#price-max").text("₹" + ui.values[1]).attr("data-max_price", ui.values[1]);
        }
    });

    // Rating bar selection
    $(".rating-container").on("click", function() {
        $(".rating-container").removeClass("active");
        $(this).addClass("active");
        selectedMinRating = parseInt($(this).data("stars"));
    });

    function renderProducts(filterData = null) {
        const grid = $('#product-grid');
        grid.empty();

        let filtered = products;

        if (filterData) {
            filtered = products.filter(p => {
                const priceMatch = p.price >= filterData.minPrice && p.price <= filterData.maxPrice;
                const ratingMatch = p.rating >= filterData.minRating;
                const stockMatch = (p.inStock && filterData.inStock) || (!p.inStock && filterData.outOfStock);
                const searchMatch = !filterData.search || p.title.toLowerCase().includes(filterData.search.toLowerCase());
                return priceMatch && ratingMatch && stockMatch && searchMatch;
            });
        }

        if (filtered.length === 0) {
            grid.append('<div class="col-12 py-5 text-center text-muted text-uppercase small tracking-widest">No matching products found.</div>');
            return;
        }

        filtered.forEach(p => {
            const card = `
                <div class="col-md-6 col-lg-4">
                    <div class="product-card">
                        <div class="product-img-wrapper">
                            ${!p.inStock ? '<div class="out-of-stock-tag">Out of Stock</div>' : ''}
                            <img src="${p.img}" alt="${p.title}" class="product-img">
                            <div class="add-to-cart-overlay">
                                <button onclick="addToCart('${p.title}')" class="btn-add-cart">Add to Cart</button>
                            </div>
                        </div>
                        <div class="text-center">
                            <h5 class="small fw-bold text-uppercase mb-1 lowercase">${p.title}</h5>
                            <p class="text-muted italic small mb-2">$${p.price.toFixed(2)}</p>
                            <div class="text-secondary" style="font-size: 0.6rem;">
                                ${'★'.repeat(p.rating)}${'☆'.repeat(5-p.rating)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            grid.append(card);
        });
    }

    $('#apply-filters, #search-btn').on('click', function() {
        renderProducts({
            minPrice: $("#price-range").slider("values", 0),
            maxPrice: $("#price-range").slider("values", 1),
            minRating: selectedMinRating,
            inStock: $('#filter-instock').is(':checked'),
            outOfStock: $('#filter-outstock').is(':checked'),
            search: $('#search-input').val()
        });
    });

    $('#reset-filters').on('click', function() {
        $("#price-range").slider("values", [0, 500]);
        $("#price-min").text("$0");
        $("#price-max").text("$500");
        $(".rating-container").removeClass("active");
        selectedMinRating = 0;
        $('#filter-instock, #filter-outstock').prop('checked', true);
        $('#search-input').val('');
        renderProducts();
    });

    renderProducts();

});