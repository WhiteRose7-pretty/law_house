<?php

return [

    'p24' => [
        'crc' => env('PLATNOSCI24_CRC'),
        'merchantId' => env('PLATNOSCI24_MERCHANT_ID'),
        'posId' => env('PLATNOSCI24_POS_ID'),
        'reportKey' => env('PLATNOSCI24_REPORT_KEY')
    ],
    'fv'  => [
        'apiKey' => env('FAKTUROWNIA_API_KEY'),
        'domain' => env('FAKTUROWNIA_ORG_DOMAIN'),
        'seller_name' => env('FAKTUROWNIA_SELLER_NAME'),
        'seller_vat_id' => env('FAKTUROWNIA_SELLER_VAT_ID'),
        'seller_post_code' => env('FAKTUROWNIA_SELLER_POST_CODE'),
        'seller_city' => env('FAKTUROWNIA_SELLER_CITY'),
        'seller_street' => env('FAKTUROWNIA_SELLER_STREET'),
    ]

];
