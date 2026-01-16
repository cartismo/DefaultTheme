<?php

return [
    'name' => 'DefaultTheme',
    'description' => 'The default Cartismo storefront theme. Clean, fast, and fully responsive.',
    'version' => '1.0.0',
    'author' => 'Cartismo',
    'type' => 'themes',
    'is_core' => true,

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    */
    'settings' => [
        // Layout
        'layout' => [
            'header_style' => 'default', // default, centered, minimal
            'footer_style' => 'default', // default, minimal, expanded
            'sidebar_position' => 'left', // left, right, none
            'container_width' => 'default', // default, wide, full
        ],

        // Colors
        'colors' => [
            'primary' => '#4F46E5',      // Indigo
            'secondary' => '#6B7280',    // Gray
            'accent' => '#F59E0B',       // Amber
            'success' => '#10B981',      // Emerald
            'warning' => '#F59E0B',      // Amber
            'danger' => '#EF4444',       // Red
            'info' => '#3B82F6',         // Blue
        ],

        // Typography
        'typography' => [
            'font_family' => 'Inter',
            'font_size_base' => '16px',
            'heading_font_family' => 'Inter',
        ],

        // Homepage
        'homepage' => [
            'show_slider' => true,
            'show_featured_categories' => true,
            'show_featured_products' => true,
            'show_new_arrivals' => true,
            'show_bestsellers' => true,
            'show_brands' => true,
            'show_newsletter' => true,
            'products_per_row' => 4,
            'featured_products_limit' => 8,
        ],

        // Product Listing
        'product_listing' => [
            'default_view' => 'grid', // grid, list
            'products_per_page' => 12,
            'show_filters' => true,
            'show_sorting' => true,
            'show_compare' => true,
            'show_wishlist' => true,
            'show_quick_view' => true,
        ],

        // Product Page
        'product_page' => [
            'gallery_style' => 'thumbnails', // thumbnails, dots, vertical
            'show_related_products' => true,
            'show_reviews' => true,
            'show_stock_status' => true,
            'show_sku' => true,
            'show_social_share' => true,
            'related_products_limit' => 4,
        ],

        // Cart & Checkout
        'cart' => [
            'show_mini_cart' => true,
            'mini_cart_style' => 'dropdown', // dropdown, sidebar
            'show_cart_totals' => true,
            'show_shipping_calculator' => true,
        ],

        // Header
        'header' => [
            'show_top_bar' => true,
            'show_search' => true,
            'show_account' => true,
            'show_wishlist' => true,
            'show_cart' => true,
            'sticky_header' => true,
            'show_categories_menu' => true,
        ],

        // Footer
        'footer' => [
            'show_newsletter' => true,
            'show_social_links' => true,
            'show_payment_icons' => true,
            'columns' => 4,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Assets
    |--------------------------------------------------------------------------
    */
    'assets' => [
        'css' => [
            'css/theme.css',
        ],
        'js' => [
            'js/theme.js',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Screenshots
    |--------------------------------------------------------------------------
    */
    'screenshots' => [
        'preview' => 'images/preview.png',
        'thumbnail' => 'images/thumbnail.png',
    ],
];
