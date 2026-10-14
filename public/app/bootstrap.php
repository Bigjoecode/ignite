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
    'phone'      => '(947) 254-1718',                    // the practice's main line (Farmington Hills)
    'phone_tel'  => '+19472541718',
    'lead_email' => '',                                  // set to email each new consultation request
    'gtm_id'     => 'GTM-TPSXGSMG',                      // Google Tag Manager container; '' turns tracking off
    'meta_pixel' => '1423486759848383',                  // Meta (Facebook) pixel; '' turns it off
    // the orthodontist, named on /meet-the-doctor/ and in the schema; '' leaves both out
    'doctor_name'       => 'Dr Hatim Alqurashi',
    'doctor_credential' => '',                           // e.g. DDS, MS — set once confirmed
    // the practice's own profiles: shown in the footer and declared as sameAs in the schema
    'social'     => [
        'Facebook'  => 'https://www.facebook.com/Igniteorthodontics',
        'Instagram' => 'https://www.instagram.com/igniteorthodontics/',
    ],
    'data_dir'   => dirname(WEBROOT) . '/ignite-data',   // outside the web root
], is_file($local) ? (require $local) : []);

require APP . '/helpers.php';
require APP . '/schema.php';
require APP . '/db.php';
require APP . '/templates.php';
require APP . '/guide-blocks.php';
