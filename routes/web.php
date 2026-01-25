<?php

// Public routes (no authentication required)
require __DIR__ . '/web/public.php';

// Character authentication and routes
require __DIR__ . '/web/character.php';

// User routes (requires web guard authentication)
require __DIR__ . '/web/user.php';

// Admin routes (requires web guard + admin role)
require __DIR__ . '/web/admin.php';

// routes (requires authentication)
require __DIR__ . '/web/auth.php';
