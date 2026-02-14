<?php

require_once __DIR__ . '/AuthController.php';

$authController = new AuthController();

$router->register('POST', '/api/auth/login', [$authController, 'login']);
$router->register('POST', '/api/auth/refresh', [$authController, 'refresh']);
$router->register('POST', '/api/auth/logout', [$authController, 'logout']);
$router->register('GET', '/api/auth/me', [$authController, 'me']);
