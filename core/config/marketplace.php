<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Server IP for A record (domain pointing)
    |--------------------------------------------------------------------------
    | Optional. If set, sellers will see this IP in "How to point your domain"
    | instructions for adding an A record. If empty, they are told to contact
    | support for the current server IP.
    */
    'server_ip' => env('MARKETPLACE_SERVER_IP', ''),
];
