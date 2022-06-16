<?php

/**
 * 
 * TW_API_ACCOUNT=newsthisbot
 * TW_API_KEY=ERXG1D80fvTO7iaLokDRehR10
 * TW_API_SECRET=ewRw9ZtzPRo0HxfkoUc3LPPdKUOnsuXSKHgY8T2cbyr2zKeBzj
 * TW_API_BEARER=AAAAAAAAAAAAAAAAAAAAAMPxdQEAAAAA1txqr0vpoplinA4IloZKMhRqEQM%3DcihCuvJ3phheo8MfkxgPtGhKbzK71Bv4GFIyxEoiWF8e0zf1wR
 * 
 * # My Account Tokens
 * TW_API_CLIENT_ID=TC01REVuWVFDVHBGY05iLTVpWEc6MTpjaQ
 * TW_API_CLIENT_SECRET=aa9kV6l7B7ihva-y9dOcIjL3MKcf_w_j9Q-bbpjbc4gB5qd5_a
 */

return [

    'account' => env('TW_API_ACCOUNT'),
    'consumer_key' => env('TW_API_KEY'),
    'consumer_secret' => env('TW_API_SECRET'),
    'bearer' => env('TW_API_BEARER'),

    'access_token' => env('TW_API_CLIENT_ID'),
    'access_token_secret' => env('TW_API_CLIENT_SECRET'),

    'bot_account' => env('TW_API_ACCOUNT', ''),
    'bot_id' => env('TW_API_BOT_ID', ''),
    'bot_secret' => env('TW_API_BOT_SECRET', ''),
    
];