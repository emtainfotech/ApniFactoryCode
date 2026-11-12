<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'razorpay' => [
        'key'            => env('RAZORPAY_KEY'),
        'secret'         => env('RAZORPAY_SECRET'),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', env('RAZORPAY_SECRET')),
    ],

    'payu' => [
        'mode'      => env('PAYU_MODE', 'auto'),
        'key'       => env('PAYU_KEY'),
        'salt'      => env('PAYU_SALT'),
        'prod_key'  => env('PAYU_PROD_KEY'),
        'prod_salt' => env('PAYU_PROD_SALT'),
        'uat_key'   => env('PAYU_UAT_KEY'),
        'uat_salt'  => env('PAYU_UAT_SALT'),
    ],

    'whatsapp' => [
        'phone_number_id'         => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token'            => env('WHATSAPP_ACCESS_TOKEN'),
        'api_version'             => env('WHATSAPP_API_VERSION', 'v22.0'),
        'otp_template'            => env('WHATSAPP_OTP_TEMPLATE', 'app_otp_verification'),
        'otp_language'            => env('WHATSAPP_OTP_LANG', 'en'),
        'otp_has_button'          => env('WHATSAPP_OTP_HAS_BUTTON', true),
        'order_status_template'   => env('WHATSAPP_ORDER_STATUS_TEMPLATE', 'order_status'),
        'order_status_language'   => env('WHATSAPP_ORDER_STATUS_LANG', 'en'),
        'order_received_template' => env('WHATSAPP_ORDER_RECEIVED_TEMPLATE', 'order_received'),
        'order_received_language' => env('WHATSAPP_ORDER_RECEIVED_LANG', 'en_US'),
        'invoice_template'        => env('WHATSAPP_INVOICE_TEMPLATE', 'invoice_uploaded_seller'),
        'invoice_language'        => env('WHATSAPP_INVOICE_LANG', 'en_US'),
        'welcome_template'        => env('WHATSAPP_WELCOME_TEMPLATE', ''),
        'welcome_language'        => env('WHATSAPP_WELCOME_LANG', 'en'),
    ],

    'crm' => [
        'base_url'      => env('APNI_CRM_BASE_URL', 'https://crm.apnifactory.co.in'),
        'gst_check_url' => env('APNI_CRM_GST_CHECK_URL', 'https://crm.apnifactory.co.in/core/api/mobile/gst-check/'),
        'api_token'     => env('APNI_CRM_API_TOKEN'),
    ],

];
