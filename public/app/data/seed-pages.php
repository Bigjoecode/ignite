<?php
// One-time seed for the pages table (app/db.php). After the first run the pages
// are managed in /admin/pages/ and this file is no longer read.
//
// Anything left out of 'data' falls back to the template's own default text,
// so the office pages below are the template defaults plus each office's details.

$office = static fn(string $street, string $city, string $zip, string $email): array => [
    'office' => [
        'street' => $street, 'city' => $city, 'state' => 'MI', 'zip' => $zip,
        'phone' => '(xxx) xxx-xxxx', 'email' => $email, 'hours' => '',
    ],
];

return [
    'locations' => [
        [
            'slug' => 'sterling-heights', 'title' => 'Sterling Heights', 'template' => 'location-full', 'menu_order' => 1,
            'description' => 'Braces and clear aligners at Ignite Orthodontics Sterling Heights. Book a no-cost consultation.',
            'data' => $office('8130 Constitution Blvd', 'Sterling Heights', '48313', 'sterlingheights@igniteorthodontics.com'),
        ],
        [
            'slug' => 'madison-heights', 'title' => 'Madison Heights', 'template' => 'location-full', 'menu_order' => 2,
            'description' => 'Braces and clear aligners at Ignite Orthodontics Madison Heights. Book a no-cost consultation.',
            'data' => $office('1190 E 12 Mile Rd', 'Madison Heights', '48071', 'madisonheights@igniteorthodontics.com'),
        ],
        [
            'slug' => 'farmington-hills', 'title' => 'Farmington Hills', 'template' => 'location-full', 'menu_order' => 3,
            'description' => 'Braces and clear aligners at Ignite Orthodontics Farmington Hills. Book a no-cost consultation.',
            'data' => $office('31700 W 12 Mile Rd', 'Farmington Hills', '48334', 'farmingtonhills@igniteorthodontics.com'),
        ],
        [
            'slug' => 'flint', 'title' => 'Flint', 'template' => 'location-full', 'menu_order' => 4,
            'description' => 'Braces and clear aligners at Ignite Orthodontics Flint. Book a no-cost consultation.',
            'data' => $office('G3535 Beecher Rd, Ste A', 'Flint', '48532', 'flint@igniteorthodontics.com'),
        ],
    ],

    'services' => [
        // the spotlight template's own defaults are this page, so it needs no data
        [
            'slug' => 'braces-for-kids', 'title' => 'Braces for Kids', 'template' => 'service-spotlight',
            'menu' => 1, 'menu_order' => 1,
            'description' => 'Gentle, affordable braces for kids and teens. Early growth guidance, flexible payment plans and stress-free orthodontic care for children.',
            'seo_title' => 'Braces for Kids & Early Orthodontics | Ignite Orthodontics',
            'image' => '/assets/img/istockphoto-1202583861-612x612-1.webp',
            'data' => [],
        ],
        [
            'slug' => 'braces-for-teens', 'title' => 'Braces for Teens', 'template' => 'service-simple',
            'menu' => 1, 'menu_order' => 2,
            'description' => 'Braces and clear aligners for teenagers at Ignite Orthodontics. Book a no-cost consultation at one of our Michigan offices.',
            'data' => [
                'hero' => [
                    'kicker' => 'Teen Orthodontics',
                    'heading' => 'Braces That Fit *Teen Life.*',
                    'lead' => 'The teen years are a common time to start orthodontic treatment, once most permanent teeth have come in.',
                ],
                'body' => ['html' =>
                    '<h2>Options teens actually want</h2>'
                    . '<p>Choose metal braces in your own colors, tooth-colored ceramic braces, or clear aligners that are removable for meals and special events.</p>'
                    . '<h2>Sports and music</h2>'
                    . '<p>Teens with braces can keep playing sports and instruments. We recommend a mouthguard during contact sports to protect teeth and lips.</p>'
                    . '<h2>What to expect</h2>'
                    . '<p>Your consultation includes an exam and a personalized treatment plan with clear timelines and payment options, before you commit to anything.</p>',
                ],
            ],
        ],
        [
            'slug' => 'braces-for-adults', 'title' => 'Braces for Adults', 'template' => 'service-simple',
            'menu' => 1, 'menu_order' => 3,
            'description' => 'Adult braces and clear aligners at Ignite Orthodontics. Discreet options, flexible payments and no-cost consultations.',
            'data' => [
                'hero' => [
                    'kicker' => 'Adult Orthodontics',
                    'heading' => 'It Is Never Too Late For *a Straighter Smile.*',
                    'lead' => 'Healthy teeth can be moved at any age. Many of our patients start treatment as adults.',
                ],
                'body' => ['html' =>
                    '<h2>Discreet treatment options</h2>'
                    . '<p>Ceramic braces and clear aligners are popular with adults who want treatment to be less noticeable at work and in everyday life.</p>'
                    . '<h2>More than appearance</h2>'
                    . '<p>Correcting crowding, gaps and bite problems can make teeth easier to clean and help them wear more evenly.</p>'
                    . '<h2>Planned around your life</h2>'
                    . '<p>We offer convenient appointment times and flexible payment options, and we will explain your insurance benefits before treatment begins.</p>',
                ],
            ],
        ],
        [
            'slug' => 'types-of-braces', 'title' => 'Types of Braces', 'template' => 'service-simple',
            'menu' => 1, 'menu_order' => 4,
            'description' => 'Compare traditional metal braces, gold braces and ceramic braces at Ignite Orthodontics.',
            'data' => [
                'hero' => [
                    'kicker' => 'Choose Your Style',
                    'heading' => 'Types of *Braces.*',
                    'lead' => 'Every type of braces we offer is effective. The right choice depends on your goals, your bite and how you want your treatment to look.',
                ],
                'body' => ['html' =>
                    '<h2>Traditional metal braces</h2>'
                    . '<p>Metal braces use durable stainless steel brackets and archwires. They are the most cost-effective way to correct alignment and work well for complex cases.</p>',
                ],
                'links' => ['items' => [
                    ['label' => 'Traditional Metal Braces', 'text' => 'A proven, cost-effective approach for a wide range of orthodontic concerns.', 'link' => '/types-of-braces/#traditional-metal-braces'],
                    ['label' => 'Gold Braces', 'text' => 'The reliability of traditional braces with a polished gold finish.', 'link' => '/gold-braces/'],
                    ['label' => 'Ceramic Braces', 'text' => 'Tooth-colored brackets for a more subtle appearance.', 'link' => '/ceramic-braces/'],
                    ['label' => 'Clear Aligners', 'text' => 'Removable trays that straighten teeth without brackets and wires.', 'link' => '/clear-aligners/'],
                ]],
            ],
        ],
        [
            'slug' => 'clear-aligners', 'title' => 'Clear Aligners', 'template' => 'service-simple',
            'menu' => 1, 'menu_order' => 5,
            'description' => 'Clear aligners at Ignite Orthodontics: removable, discreet trays that straighten teeth without brackets and wires.',
            'data' => [
                'hero' => [
                    'kicker' => 'Clear Aligner Treatment',
                    'heading' => 'Clear *Aligners.*',
                    'lead' => 'Removable, discreet trays that gradually straighten your teeth without traditional brackets and wires.',
                ],
                'body' => ['html' =>
                    '<h2>How aligners work</h2>'
                    . '<p>You wear a series of custom clear trays, each one moving your teeth a small step closer to their final position.</p>'
                    . '<h2>Removable for meals</h2>'
                    . '<p>Aligners come out to eat, brush and floss, so there are no food restrictions. They work best when worn as directed, most of the day.</p>'
                    . '<h2>Options we offer</h2>'
                    . '<p>We offer clear aligner systems including Invisalign&#174; and 3M&#8482; Clear Aligners. Your orthodontist will recommend the best option for your case.</p>',
                ],
            ],
        ],
        [
            'slug' => 'invisalign', 'title' => 'Invisalign', 'template' => 'service-simple',
            'menu' => 1, 'menu_order' => 6,
            'description' => 'Invisalign clear aligners for teens and adults at Ignite Orthodontics. Book a no-cost consultation.',
            'data' => [
                'hero' => [
                    'kicker' => 'Clear Aligner Treatment',
                    'heading' => '*Invisalign®* Clear Aligners.',
                    'lead' => 'Invisalign® uses a series of custom clear aligners to straighten teeth discreetly, without brackets or wires.',
                ],
                'body' => ['html' =>
                    '<h2>Virtually invisible</h2>'
                    . '<p>The clear trays are hard to notice when you smile or talk, which makes Invisalign&#174; popular with teens and adults.</p>'
                    . '<h2>Planned by an orthodontist</h2>'
                    . '<p>Your orthodontist designs and monitors your treatment plan and adjusts it as your teeth move.</p>',
                ],
            ],
        ],
        [
            'slug' => 'gold-braces', 'title' => 'Gold Braces', 'template' => 'service-simple',
            'menu' => 0, 'menu_order' => 7,
            'description' => 'Gold braces at Ignite Orthodontics: the reliability of traditional braces with a polished gold finish.',
            'data' => [
                'hero' => [
                    'kicker' => 'Types of Braces',
                    'heading' => 'Gold *Braces.*',
                    'lead' => 'Gold braces offer the reliability of traditional braces with a polished gold finish, for patients who want their treatment to stand out.',
                ],
                'body' => ['html' =>
                    '<h2>How gold braces work</h2>'
                    . '<p>Gold braces work the same way as traditional metal braces: brackets and archwires apply gentle, steady pressure to move teeth into place over time.</p>'
                    . '<h2>Is it right for me?</h2>'
                    . '<p>Your orthodontist will review your bite and goals at your consultation and explain whether gold braces are a good fit.</p>',
                ],
            ],
        ],
        [
            'slug' => 'ceramic-braces', 'title' => 'Ceramic Braces', 'template' => 'service-simple',
            'menu' => 0, 'menu_order' => 8,
            'description' => 'Tooth-colored ceramic braces at Ignite Orthodontics for effective, more subtle orthodontic treatment.',
            'data' => [
                'hero' => [
                    'kicker' => 'Types of Braces',
                    'heading' => 'Ceramic *Braces.*',
                    'lead' => 'Tooth-colored brackets offer effective orthodontic treatment with a more subtle appearance.',
                ],
                'body' => ['html' =>
                    '<h2>Blends in with your smile</h2>'
                    . '<p>Ceramic brackets are designed to match the color of your teeth, which makes them less noticeable than metal braces.</p>'
                    . '<h2>A popular choice for teens and adults</h2>'
                    . '<p>Ceramic braces are often chosen by patients who want the precision of braces with a more discreet look.</p>',
                ],
            ],
        ],
    ],
];
