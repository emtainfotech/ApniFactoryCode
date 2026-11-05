<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Buyer Receipt Acknowledgement & Dispute Policy Window
    |--------------------------------------------------------------------------
    | The BRD Section 11 does not specify a hardcoded statutory window for
    | receipt acknowledgement or dispute filing in B2B wholesale freight.
    | To avoid inventing policy, if policy_window_enabled is false or days is null,
    | no artificial deadline is enforced. If enabled, it is validated server-side.
    */
    'policy_window_enabled'      => env('DISPUTE_POLICY_WINDOW_ENABLED', false),
    'policy_window_days'         => env('DISPUTE_POLICY_WINDOW_DAYS', null), // e.g. 7, 14 or null

    /*
    |--------------------------------------------------------------------------
    | Permitted Dispute Categories (Standard B2B Wholesale)
    |--------------------------------------------------------------------------
    */
    'categories' => [
        'shortage'   => 'Shortage / Missing Line Items or Units',
        'damage'     => 'Physical Transit Damage to Cartons / Goods',
        'wrong_item' => 'Incorrect Item / Variant Received',
        'other'      => 'Other Discrepancy / Transit Issue',
    ],

    /*
    |--------------------------------------------------------------------------
    | Evidence Attachment Rules
    |--------------------------------------------------------------------------
    */
    'max_attachments_per_claim' => 5,
    'max_file_size_kb'          => 5120, // 5MB per attachment
    'allowed_mime_types'        => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resolution Automation Guard (Strict BRD Compliance)
    |--------------------------------------------------------------------------
    | In accordance with the BRD restriction, the platform does NOT automatically
    | decide financial liability, issue automated refunds, or trigger return pickups.
    | All resolution workflows require human operational review.
    */
    'auto_resolution_enabled'   => false,
    'require_admin_approval'    => true,
];

