<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 6.1 & 6.2: Field Sales Agent, Attribution & Incentive Configuration
    |--------------------------------------------------------------------------
    | BRD Reference: Section 12, 12.1, 12.2, Section 21, Section 9, Section 13.
    |
    | CRITICAL COMMERCIAL POLICY CONFLICT & GOVERNANCE:
    | - Section 12 & Section 21 describe an Apni Factory-funded buyer incentive
    |   paid across the buyer's first 3 eligible orders, with NO seller deductions.
    | - Another BRD passage describes a seller-funded incentive arrangement and lists
    |   unresolved questions regarding payer and commercial structure.
    | - Per architectural rule, seller-funded deductions are strictly PROHIBITED.
    | - Non-financial order eligibility tracking (0/3..3/3) may run in Phase 6.2,
    |   while payable financial incentive generation remains BLOCKED by default
    |   until written business & finance approval is recorded below.
    */

    'incentive_funding_model' => env('AGENT_INCENTIVE_FUNDING_MODEL', 'apnifactory_funded'),

    // MUST remain false until written business approval confirms the funding arrangement.
    // Prevents payable incentive liabilities from being generated prematurely.
    'funding_policy_approved' => env('AGENT_FUNDING_POLICY_APPROVED', false),

    // Controls live payable incentive ledger generation alongside approved rules.
    'incentive_calculation_enabled' => env('AGENT_INCENTIVE_CALCULATION_ENABLED', false),

    // Enforces Section 12.1 & 21 guarantee: no agent incentive may ever be deducted from seller proceeds.
    'seller_deductions_prohibited' => true,

    // Phase 6.2 non-financial eligible-order tracking (0/3 through 3/3).
    // When false, Agent portal safely renders Unavailable (Phase 6.1 compatibility mode).
    'eligible_order_tracking_implemented' => env('AGENT_ELIGIBLE_ORDER_TRACKING_ENABLED', true),

    // Maximum primary agents that may be concurrently active for a single buyer/dealer account
    'max_primary_agents_per_buyer' => 1,

    // First N eligible orders across any manufacturer/seller (Section 12 scheme cap)
    'eligible_orders_limit' => 3,

    // Order eligibility policy parameters (configurable pending formal business ratification)
    'eligibility_order_unit'          => env('AGENT_ELIGIBILITY_UNIT', 'parent_order'),
    'eligibility_qualifying_event'    => env('AGENT_ELIGIBILITY_EVENT', 'buyer_acknowledged_or_delivered'),
    'eligibility_cooling_window_days' => env('AGENT_ELIGIBILITY_WINDOW_DAYS', null),
    'partial_refund_policy'           => env('AGENT_PARTIAL_REFUND_POLICY', 'pending_review'),
    'disputed_order_policy'           => env('AGENT_DISPUTED_ORDER_POLICY', 'pending_review'),
    'require_attribution_prior_to_order' => env('AGENT_REQUIRE_PRIOR_ATTRIBUTION', false),

    // Stage 7 Dashboard visibility guard: hide currency totals from agents until approved
    'expose_agent_incentive_amounts' => env('AGENT_EXPOSE_INCENTIVE_AMOUNTS', false),

    // Phase 6.3 Payout Governance Controls (MUST remain disabled / awaiting policy until written sign-off)
    // execution_mode: 'awaiting_policy' (blocks financial execution/mark-paid),
    //                 'manual_finance_recording' (only after written business/finance approval of manual bank/UTR workflow)
    'payout_execution_enabled'        => env('AGENT_PAYOUT_EXECUTION_ENABLED', false),
    'payout_execution_mode'           => env('AGENT_PAYOUT_EXECUTION_MODE', 'awaiting_policy'),
    'payout_require_kyc_verified'     => env('AGENT_PAYOUT_REQUIRE_KYC', false),
    'payout_require_active_contract'  => env('AGENT_PAYOUT_REQUIRE_CONTRACT', false),
    'payout_tax_withholding_approved' => env('AGENT_PAYOUT_TAX_WITHHOLDING_APPROVED', false),
    'payout_hold_days'                => env('AGENT_PAYOUT_HOLD_DAYS', null),
    'payout_dual_approval_required'   => env('AGENT_PAYOUT_DUAL_APPROVAL', null),
    'payout_minimum_threshold'        => env('AGENT_PAYOUT_MIN_THRESHOLD', null),

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

    // Attribution approval workflow configuration
    'attribution_requires_approval' => env('AGENT_ATTRIBUTION_REQUIRES_APPROVAL', false),

    // Reassignment policy guard (BRD Section 12)
    'allow_reassignment_after_eligible_order_exception' => env('AGENT_REASSIGNMENT_EXCEPTION_ALLOWED', false),

    // Buyer-facing UI exposure (Stage 7)
    'expose_agent_to_buyer' => env('AGENT_EXPOSE_TO_BUYER', false),

    // Notifications (Stage 10): restricted to In-App & Email ONLY unless SMS/WhatsApp approved
    'notification_channels' => [
        'in_app'   => true,
        'email'    => true,
        'sms'      => false,
        'whatsapp' => false,
    ],
];
