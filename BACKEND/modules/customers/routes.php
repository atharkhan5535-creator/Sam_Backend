<?php

require_once __DIR__ . '/CustomerController.php';

$customerController = new CustomerController();

$router->register('POST', '/api/customers/register', [$customerController, 'register']);
