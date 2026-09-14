<?php
declare(strict_types=1);

date_default_timezone_set('America/Detroit'); // booking dates follow the offices' local day

define('APP', __DIR__);
define('WEBROOT', dirname(__DIR__));

// config.local.php is server-only (never committed, never overwritten by deploys)
$local  = APP . '/config.local.php';
$config = array_merge([
    'site_name'  => 'Ignite Orthodontics',
    'base_url'   => 'https://igniteorthodontics.com',
    'phone'      => '(586) 393-7972',
    'phone_tel'  => '+15863937972',
    'lead_email' => '',                                  // set to email each new consultation request
    'data_dir'   => dirname(WEBROOT) . '/ignite-data',   // outside the web root
], is_file($local) ? (require $local) : []);

require APP . '/helpers.php';
