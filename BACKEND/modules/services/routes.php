<?php

require_once __DIR__ . '/ServiceController.php';
require_once __DIR__ . '/../../middlewares/authenticate.php';
require_once __DIR__ . '/../../middlewares/authorize.php';

$controller = new ServiceController();

$router->register(
    'POST',
    '/api/services',
    [$controller, 'create'],
    [
        'authenticate',
        function () {
            authorize(['ADMIN']);
        }
    ]
);

$router->register(
    'GET',
    '/api/services',
    [$controller, 'index'],
    [
        'authenticate',
        function () {
            authorize(['ADMIN', 'STAFF', 'CUSTOMER']);
        }
    ]
);
