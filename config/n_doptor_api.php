<?php
return [
    'auth' => [
        'client_login_url' => env('NDOPTOR_API_URL', '') . '/client/login',
        'client_id' => env('DOPTOR_CLIENT_ID', ''),
        'client_pass' => env('DOPTOR_CLIENT_PASS', ''),
    ],
    'widget' => env('NDOPTOR_API_URL', '') . '/switch/widget',

    'offices' => env('NDOPTOR_API_URL', '') . '/offices',
    'office_unit_designation_map' => env('NDOPTOR_API_URL', '') . '/office/unit-designation-map',
    'office_unit_designation_employee_map' => env('NDOPTOR_API_URL', '') . '/office/unit-designation-employee-map',
];
