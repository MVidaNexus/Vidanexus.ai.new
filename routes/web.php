<?php

/**
 * Web routes are split by area under routes/web/ for easier navigation.
 * Laravel still loads this file with the `web` middleware group (see bootstrap/app.php).
 */

require __DIR__.'/web/public.php';
require __DIR__.'/web/auth.php';
require __DIR__.'/web/dashboard.php';
require __DIR__.'/web/payment.php';
require __DIR__.'/web/admin.php';
