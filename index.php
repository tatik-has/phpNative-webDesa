<?php

date_default_timezone_set('Asia/Jakarta'); 

session_start();

require_once __DIR__ . '/logic_tier/router/routes.php';

Router::dispatch();