<?php

return [
    // API
    'api_domain' => 'https://billing.ghofi.com.cy/',
    'api_key' => '6686f8a9ad9a6d91df87a6ea664ccdaa',
    'api_secret' => '598362efa141b6915bbfa1b4c1d0f1fc',

    // Splynx url (without last slash)
    'splynx_url' => 'http://194.30.140.30/',

    // Jcc credentials
    // You can get it by creating REST API app on page "My Apps & Credentials" in Jcc developer
    // account (https://developer.Jcc.com/developer/applications/)
    'clientId' => '',
    'clientSecret' => '',
	'clientPassword' => '',
    'isProduction' => true,    

    // Get payment method "Jcc" at `Config / Finance / Payment methods` and enter it's id here
    // Or simply use existing method id
    'payment_method_id' => 8,

    // Service fee (%)
    'serviceFee' => 0,

    // Add fee (position) to request
    'add_fee_request' => false,

    // If you set service add fee to invoice - set description for Invoice item
    'fee_message' => 'Jcc commission',

    // Fee VAT (%)
    'fee_VAT' => 0,

    // If you set service fee - set id for category of fee transactions (look at `Config / Finance / Transaction categories`)
    'transaction_fee_category' => 5,

    // Group bank statements by `month` or `day`
    'bank_statements_group' => 'month',

    // CookieValidationKey
    'cookieValidationKey' => 'PayWmUit9UhfYcb0YE91SQg-dkR72p2Z',

    // Required for encrypting add-on settings with type 'encrypted'
    // Don't change and delete this param
    'add_on_encryption_key' => 'wTqTLG8XJ5CgtZddvwrr8uN'
];
