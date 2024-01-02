<?php

return [
    /*
     // Structure: array key - is partner_id
     // If partner not set in this array - default params will be used.
     \splynx\helpers\ConfigHelper::PARTNERS_SETTINGS_FIELD => [
         1 => [

             // You can set clientId and clientSecret for separate partner.
             'partners_account' => [
                 'clientId' => '',
                 'clientSecret' => ''
             ],

             // You can declare partnerCurrency
             'partnerCurrency' => 'USD',

             // You can declare partner payment method
             'payment_method_id' => 2,
         ],
         2 => [
             'partners_account' => [
                 1 => [
                     'clientId' => '',
                     'clientSecret' => ''
                 ]
             ],
             'partnerCurrency' => 'EUR',
             'payment_method_id' => 3,
         ],
         3 => [
             'partners_account' => [
                 1 => [
                     'clientId' => '',
                     'clientSecret' => ''
                 ]
             ],
             'partnerCurrency' => 'UAH',
             'payment_method_id' => 4,
         ]
     ]
     */
    'add_on_encryption_key' => ''
];
