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
        'key'  => env('PAYU_KEY'),
        'salt' => env('PAYU_SALT'),
    ],

    'whatsapp' => [
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'access_token'    => env('WHATSAPP_ACCESS_TOKEN'),
        'api_version'     => env('WHATSAPP_API_VERSION', 'v22.0'),
    ],

    'crm' => [
        'base_url'      => env('APNI_CRM_BASE_URL', 'https://crm.apnifactory.co.in'),
        'gst_check_url' => env('APNI_CRM_GST_CHECK_URL', 'https://crm.apnifactory.co.in/core/api/mobile/gst-check/'),
        'api_token'     => env('APNI_CRM_API_TOKEN'),
    ],

];
