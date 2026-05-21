<?php
/**
 * Emergency WooCommerce 9.x Compatibility Shim
 * Provides missing classes when WooCommerce files are mismatched.
 * 
 * If your database expects WooCommerce 9.x but your files are 8.x,
 * this prevents fatal errors by defining the missing classes.
 * 
 * Ideally: Update your WooCommerce plugin files to match your database.
 * This shim is a temporary band-aid.
 */

// Only run if WooCommerce is active
if (!defined('WC_ABSPATH')) {
    return;
}

// Prevent fatal error: ProductType enum
if (PHP_VERSION_ID >= 80100 && !enum_exists('Automattic\WooCommerce\Enums\ProductType')) {
    eval('
    namespace Automattic\WooCommerce\Enums;
    enum ProductType: string {
        case SIMPLE = "simple";
        case GROUPED = "grouped";
        case EXTERNAL = "external";
        case VARIABLE = "variable";
        case VARIATION = "variation";
        case SUBSCRIPTION = "subscription";
        case VARIABLE_SUBSCRIPTION = "variable_subscription";
    }
    ');
}

// Prevent fatal error: ProductStatus enum
if (PHP_VERSION_ID >= 80100 && !enum_exists('Automattic\WooCommerce\Enums\ProductStatus')) {
    eval('
    namespace Automattic\WooCommerce\Enums;
    enum ProductStatus: string {
        case PUBLISH = "publish";
        case FUTURE = "future";
        case DRAFT = "draft";
        case PENDING = "pending";
        case PRIVATE = "private";
        case TRASH = "trash";
    }
    ');
}

// Prevent fatal error: OrderStatus enum
if (PHP_VERSION_ID >= 80100 && !enum_exists('Automattic\WooCommerce\Enums\OrderStatus')) {
    eval('
    namespace Automattic\WooCommerce\Enums;
    enum OrderStatus: string {
        case PENDING = "pending";
        case PROCESSING = "processing";
        case ON_HOLD = "on-hold";
        case COMPLETED = "completed";
        case CANCELLED = "cancelled";
        case REFUNDED = "refunded";
        case FAILED = "failed";
        case CHECKOUT_DRAFT = "checkout-draft";
    }
    ');
}

// Prevent fatal error: CouponType enum
if (PHP_VERSION_ID >= 80100 && !enum_exists('Automattic\WooCommerce\Enums\CouponType')) {
    eval('
    namespace Automattic\WooCommerce\Enums;
    enum CouponType: string {
        case PERCENT = "percent";
        case FIXED_CART = "fixed_cart";
        case FIXED_PRODUCT = "fixed_product";
    }
    ');
}
