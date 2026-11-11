<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 6.1: Field Sales Agent & Buyer Attribution Configuration
    |--------------------------------------------------------------------------
    | BRD Reference: Section 12, 12.1, 12.2, Section 21, Section 3, Section 13.
    |
    | IMPORTANT COMMERCIAL POLICY GUARD:
    | - Section 12 & Section 21 describe an Apni Factory-funded agent incentive
    |   paid across the buyer's first 3 eligible orders, with NO seller deductions.
    | - Contradictory passages in the BRD mention possible seller-funded arrangements.
    | - Per architectural rule, seller-funded incentives are NOT implemented.
    | - Incentive calculation, ledger, and payout are explicitly DISABLED in Phase 6.1.
    */

    'incentive_funding_model' => 'apnifactory_funded',

    // Must remain false during Phase 6.1 (Agent Master & Buyer Attribution only)
    'incentive_calculation_enabled' => false,

    // Order eligibility engine is deferred to Phase 6.2 pending written policy approval.
    // Agent Dashboard must show eligibility progress as unavailable rather than fabricating counts.
    'eligible_order_tracking_implemented' => false,

    // Maximum primary agents that may be concurrently active for a single buyer/dealer account
    'max_primary_agents_per_buyer' => 1,

    // First N eligible orders across any manufacturer/seller (reference scheme limit from Section 12)
    'eligible_orders_limit' => 3,

    // Controlled agent statuses
    'agent_statuses' => [
        'pending'   => 'Pending Activation',
        'active'    => 'Active',
        'inactive'  => 'Inactive',
        'suspended' => 'Suspended',
    ],

    // Controlled KYC / Contract statuses (no invented document types or tax mandates)
    'kyc_statuses' => [
        'not_submitted'  => 'Not Submitted',
        'pending_review' => 'Pending Review',
        'verified'       => 'Verified',
        'rejected'       => 'Rejected',
        'expired'        => 'Expired',
    ],

    'contract_statuses' => [
        'not_signed' => 'Not Signed',
        'pending'    => 'Pending Execution',
        'active'     => 'Active',
        'terminated' => 'Terminated',
        'expired'    => 'Expired',
    ],

    // Attribution approval workflow configuration:
    // If true, admin attribution creations default to 'pending_approval' requiring separate approval.
    // If false, direct admin attribution is immediately approved unless 'require_approval' is requested.
    'attribution_requires_approval' => env('AGENT_ATTRIBUTION_REQUIRES_APPROVAL', false),

    // Reassignment policy guard (BRD Section 12):
    // Reassignment is only permitted BEFORE the buyer's first eligible order, unless
    // an explicitly approved corporate exception policy is enabled below.
    'allow_reassignment_after_eligible_order_exception' => env('AGENT_REASSIGNMENT_EXCEPTION_ALLOWED', false),

    // Buyer-facing UI exposure (Stage 7):
    // Do not expose internal agent information to buyers unless approved onboarding UX requires it.
    'expose_agent_to_buyer' => env('AGENT_EXPOSE_TO_BUYER', false),

    // Notifications (Stage 8):
    // Initially restricted to in-app database notifications and email ONLY.
    // Do NOT enable SMS or WhatsApp without written business approval.
    'notification_channels' => [
        'in_app'   => true,
        'email'    => true,
        'sms'      => false,
        'whatsapp' => false,
    ],
];

