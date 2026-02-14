<?php

// App
define('APP_ENV', 'development');

// JWT
define('JWT_SECRET', 'SAM_SUPER_SECRET_KEY_CHANGE_LATER');
define('JWT_ALGO', 'HS256');
define('JWT_EXPIRY_SECONDS', 900); // 15 min
define('REFRESH_TOKEN_EXPIRY_DAYS', 7);

// Roles
define('ROLE_SUPER_ADMIN', 'SUPER_ADMIN');
define('ROLE_ADMIN', 'ADMIN');
define('ROLE_STAFF', 'STAFF');
define('ROLE_CUSTOMER', 'CUSTOMER');
