<?php

/**
 * ENTRY POINT APLIKASI
 */

session_start();

require_once __DIR__ . '/logic_tier/router/routes.php';

Router::dispatch();