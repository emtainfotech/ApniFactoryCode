<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 7.1: Cross-Platform Integration & Synchronization Configuration
    |--------------------------------------------------------------------------
    | BRD Reference: Section 14, Section 16, Section 17, Section 18, Section 19.
    |
    | Establishes Laravel backend & MySQL as the authoritative Source of Truth
    | for Seller, Product, Variant, SKU, Category, Customer/Buyer, Price,
    | Stock, Order, Commission, and Tax entities.
    */

    // Authoritative hub identifier
    'authoritative_source' => 'laravel_backend',

    // Controlled integration channel registry
    'channels' => [
        'laravel_backend' => 'Laravel Authoritative Core',
        'flutter_mobile'  => 'Flutter Buyer Mobile App',
        'crm_external'    => 'External CRM (crm.apnifactory.co.in)',
        'admin_panel'     => 'Admin Portal (Filament & Web)',
        'seller_panel'    => 'Seller Portal (Web & API v2)',
        'public_website'  => 'Public Catalogue & Storefront APIs',
        'payment_gateway' => 'Payment Gateway Webhooks',
    ],

    // External CRM synchronization remains safely gated until CRM API contract & credentials are approved
    'crm_sync_enabled'        => env('APNI_CRM_SYNC_ENABLED', false),
    'crm_gst_check_enabled'   => env('APNI_CRM_GST_CHECK_ENABLED', true),
    'crm_timeout_seconds'     => (int) env('APNI_CRM_TIMEOUT_SECONDS', 5),

    // Security hardening gates
    'legacy_test_endpoints_enabled'    => env('ALLOW_LEGACY_TEST_PAYMENT_ENDPOINT', false),
    'allow_dev_otp_bypass'             => env('ALLOW_DEV_OTP_BYPASS', false),
    'require_legacy_webhook_signature' => env('REQUIRE_LEGACY_WEBHOOK_SIGNATURE', true),
    'legacy_attribute_projection_enabled' => env('LEGACY_ATTRIBUTE_PROJECTION_ENABLED', true),

    // Sync outbox & retry policy
    'default_max_attempts'     => 3,
    'retry_backoff_seconds'    => [15, 60, 180],

    // Conflict resolution strategy: never silent last-write-wins across independent systems
    'conflict_policy' => 'authoritative_reject_and_log_diff',

    // Entities where blind automatic retry of state-changing operations is strictly prohibited
    'non_auto_retryable_entity_types' => [
        'payment',
        'order_financial',
        'commission',
        'tax',
        'settlement',
        'payout',
    ],

    // Authoritative entity source-of-truth & canonical key specification
    'source_of_truth_matrix' => [
        'seller' => [
            'authoritative_table' => 'users + companies',
            'canonical_pk'        => 'companies.id (user_id FK)',
            'canonical_ref'       => 'companies.gst / users.email',
            'permitted_writers'   => ['admin_panel', 'seller_panel (profile-scoped)'],
            'permitted_readers'   => ['admin_panel', 'seller_panel', 'public_website (safe fields only)'],
            'conflict_rule'       => 'Admin controls status & base commission; seller edits own profile only.',
        ],
        'product' => [
            'authoritative_table' => 'products',
            'canonical_pk'        => 'products.id',
            'canonical_ref'       => 'products.slug',
            'permitted_writers'   => ['admin_panel', 'seller_panel (submission workflow)'],
            'permitted_readers'   => ['all (public/flutter filtered to approved+active)'],
            'conflict_rule'       => 'Only approval_status=approved products appear publicly.',
        ],
        'variant' => [
            'authoritative_table' => 'product_variants',
            'canonical_pk'        => 'product_variants.id',
            'canonical_ref'       => 'product_variants.variant_sku',
            'permitted_writers'   => ['admin_panel', 'seller_panel (Phase-II catalogue services)'],
            'permitted_readers'   => ['all'],
            'conflict_rule'       => 'Unique combo (product_id, shade_card_id, box_packing_id); no duplicate SKU.',
        ],
        'sku' => [
            'authoritative_table' => 'seller_listings',
            'canonical_pk'        => 'seller_listings.id',
            'canonical_ref'       => 'seller_listings.seller_sku',
            'permitted_writers'   => ['seller_panel', 'admin_panel'],
            'permitted_readers'   => ['admin_panel', 'seller_panel (full)', 'public_website (PublicListingResource only)'],
            'conflict_rule'       => 'Legacy product_attributes synced only via explicit mapping; discrepancies logged.',
        ],
        'category' => [
            'authoritative_table' => 'main_categories + categories + sub_categories',
            'canonical_pk'        => 'categories.id',
            'canonical_ref'       => 'categories.name',
            'permitted_writers'   => ['admin_panel'],
            'permitted_readers'   => ['all'],
            'conflict_rule'       => 'Admin master authority; seller category requests cannot auto-publish.',
        ],
        'customer' => [
            'authoritative_table' => 'customers',
            'canonical_pk'        => 'customers.id',
            'canonical_ref'       => 'customers.email / customers.mobile',
            'permitted_writers'   => ['flutter_mobile (self-profile)', 'admin_panel'],
            'permitted_readers'   => ['admin_panel', 'flutter_mobile (own)', 'field_agent (assigned masked only)', 'seller_panel (order ship-to only)'],
            'conflict_rule'       => 'External CRM cannot overwrite Laravel customer identity silently.',
        ],
        'price' => [
            'authoritative_table' => 'seller_listings + price_revisions',
            'canonical_pk'        => 'seller_listings.id',
            'canonical_ref'       => 'seller_listings.selling_price',
            'permitted_writers'   => ['seller_panel (proposal)', 'admin_panel (approval)', 'laravel_backend (DynamicPriceRevisionService)'],
            'permitted_readers'   => ['admin_panel & owner seller (seller_price + selling_price)', 'public_website & flutter_mobile (selling_price only)'],
            'conflict_rule'       => 'Server-side engine derives customer selling_price; channel calculations rejected.',
        ],
        'stock' => [
            'authoritative_table' => 'seller_listings + inventory_movements',
            'canonical_pk'        => 'seller_listings.id',
            'canonical_ref'       => 'stock_quantity - reserved_stock',
            'permitted_writers'   => ['seller_panel', 'admin_panel', 'laravel_backend (InventoryService)'],
            'permitted_readers'   => ['all (public receives availability/status)'],
            'conflict_rule'       => 'Row-locked InventoryService movements; concurrent drift logged to reconciliation.',
        ],
        'order' => [
            'authoritative_table' => 'orders + seller_fulfilments + transections',
            'canonical_pk'        => 'orders.id',
            'canonical_ref'       => 'orders.orderno',
            'permitted_writers'   => ['laravel_backend', 'flutter_mobile (checkout init)', 'seller_panel (fulfilment)', 'payment_gateway (verified webhooks)'],
            'permitted_readers'   => ['admin_panel', 'buyer (own)', 'seller (own fulfilments)', 'field_agent (assigned non-financial 0/3..3/3)'],
            'conflict_rule'       => 'Payment transitions require verified cryptographic callback; no client mark-paid.',
        ],
        'commission' => [
            'authoritative_table' => 'commission_rules + order_commission_snapshots',
            'canonical_pk'        => 'commission_rules.id / order_commission_snapshots.id',
            'canonical_ref'       => 'commission_rules.rule_code',
            'permitted_writers'   => ['admin_panel (dual approval)', 'laravel_backend (CommissionCalculationService)'],
            'permitted_readers'   => ['admin_panel', 'seller_panel (own agreements/snapshots ONLY)'],
            'conflict_rule'       => 'Snapshots immutable post-order; external channels & public blocked.',
        ],
        'tax' => [
            'authoritative_table' => 'tax_masters + order_tax_snapshots + invoices',
            'canonical_pk'        => 'tax_masters.id / order_tax_snapshots.id',
            'canonical_ref'       => 'tax_masters.hsn_code',
            'permitted_writers'   => ['admin_panel (TaxMasterService)', 'laravel_backend (TaxCalculationService)'],
            'permitted_readers'   => ['admin_panel', 'seller_panel', 'buyer (order invoices)'],
            'conflict_rule'       => 'Tax rates immutable in snapshots; external override rejected & audited.',
        ],
    ],
];

