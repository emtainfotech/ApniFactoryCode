<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Marketplace Tax & Legal Invoice Model Configuration
    |--------------------------------------------------------------------------
    | In accordance with the approved BRD Section 9.4, developers must not
    | assume ApniFactory is the seller of goods. The principal-to-principal
    | model determines document ownership and statutory obligations.
    */

    'model' => env('MARKETPLACE_LEGAL_MODEL', 'principal_to_principal'),

    // Goods Tax Invoice Issuer: 'seller' (Principal Manufacturer/Seller)
    'goods_invoice_issuer' => 'seller',

    // Platform Commission Invoice Issuer: 'platform' (ApniFactory B2B Marketplace)
    'commission_invoice_issuer' => 'platform',

    // Document numbering prefixes
    'goods_invoice_prefix'      => 'INV-G-',
    'commission_invoice_prefix' => 'INV-C-',

    // Enforce approved HSN classification on checkout
    'strict_hsn_validation'     => true,

    // Payout hold period in days after verified delivery / payment
    'payout_hold_days'          => 7,

    // Platform legal entity details for commission invoices
    'platform_legal_name'       => 'Apni Factory Private Limited',
    'platform_gstin'            => env('APNIFACTORY_GSTIN', '07AAAAA0000A1Z5'),
    'platform_state'            => 'Delhi',
    'platform_address'          => 'Apni Factory Corporate Tower, Phase II, New Delhi, India',
];

