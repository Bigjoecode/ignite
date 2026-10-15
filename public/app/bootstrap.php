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
    // The orthodontist. Shown on /meet-the-doctor/, on the office pages listed in
    // doctor_offices and on those offices' landing pages, and named in the schema.
    // An empty doctor_name leaves him out everywhere.
    'doctor_name'       => 'Dr. Hatim Alqurashi',
    'doctor_credential' => 'PhD, MSc, MSD — Board-Certified Orthodontist',
    'doctor_photo'      => 'dr-hatim-alqurashi.jpg',     // in assets/img
    'doctor_photo_size' => [600, 801],
    'doctor_bio'        => [
        'Dr. Hatim Alqurashi is an orthodontist and researcher with expertise in tissue engineering, '
            . 'dental biomaterials, and orthodontics.',
        'He earned a PhD in Clinical Dentistry with a focus on tissue engineering and regenerative '
            . 'medicine, as well as a Master of Science in Dental Materials, from the University of '
            . 'Sheffield, United Kingdom.',
        'He subsequently completed advanced orthodontic training and a Master of Science in Dentistry '
            . '(MSD) in Orthodontics at Boston University, where he also achieved American Board of '
            . 'Orthodontics certification.',
    ],
    'doctor_badges'     => [
        'American Board of Orthodontics certified',
        'MSD in Orthodontics, Boston University',
        'PhD in Clinical Dentistry, University of Sheffield',
    ],
    // the offices whose pages and landing pages carry his profile
    'doctor_offices'    => ['farmington-hills', 'madison-heights', 'sterling-heights'],
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
