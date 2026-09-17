<?php
declare(strict_types=1);

/**
 * Page templates (layouts) for service and location pages.
 *
 * Each template lists the sections it renders, in order, and each section lists
 * the fields the admin can edit. The dashboard builds its forms from this file
 * and the public site renders the same data through the template's "family"
 * view in app/views/templates/.
 *
 * Field types: text, textarea, richtext, lines, image (also stores "<key>_alt"),
 *              select, list (repeating group of fields).
 *
 * Location templates may use the tokens {office} and {city} in any text; they
 * are replaced with the office name and city when the page is rendered.
 */

function templates(): array
{
    static $templates;
    if ($templates !== null) {
        return $templates;
    }

    /* ---------- pieces shared by several templates ---------- */

    $faqSection = static function (array $items, string $heading = 'Frequently Asked *Questions*'): array {
        return [
            'label' => 'Questions & answers',
            'help'  => 'Shown as an accordion, and offered to Google as FAQ results.',
            'fields' => [
                ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Still Have Questions?'],
                ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => $heading],
                ['key' => 'items', 'type' => 'list', 'label' => 'Questions', 'item' => 'Question', 'max' => 20, 'fields' => [
                    ['key' => 'q', 'type' => 'text', 'label' => 'Question'],
                    ['key' => 'a', 'type' => 'textarea', 'label' => 'Answer'],
                ], 'default' => $items],
            ],
        ];
    };

    $consultSection = [
        'label' => 'Book a no-cost consultation',
        'help'  => 'The orange booking band used across the site. The buttons open the booking popup.',
        'fields' => [
            ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/IMG_20260814_125716.jpg'],
        ],
    ];

    $treatmentItems = [
        ['title' => 'Metal Braces', 'text' => 'The most cost-effective way to correct alignment, using durable stainless steel brackets and archwires.', 'image' => '/assets/img/01-image.webp', 'link' => '/types-of-braces/traditional-braces/'],
        ['title' => 'Gold Braces', 'text' => 'All the reliability of traditional braces, finished with gold-polished brackets for a warmer look.', 'image' => '/assets/img/02-image.webp', 'link' => '/gold-braces/'],
        ['title' => 'Clear Aligners', 'text' => 'Our best option for virtually invisible treatment, including Invisalign® and 3M clear aligners.', 'image' => '/assets/img/03-image.webp', 'link' => '/clear-aligners/'],
        ['title' => 'Ceramic Braces', 'text' => 'Tooth-coloured brackets that reposition teeth discreetly, blending in with your natural smile.', 'image' => '/assets/img/img1-1.jpg', 'link' => '/types-of-braces/ceramic-braces/'],
    ];

    $treatmentsSection = static function (string $heading, array $items): array {
        return [
            'label' => 'Treatment options',
            'help'  => 'A row of cards. Each card can link to another page on this site.',
            'fields' => [
                ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Choose Your Style, Enjoy Your Smile'],
                ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => $heading],
                ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'Make a lasting impression with your dream smile. We offer a full range of treatment options for children, teens and adults.'],
                ['key' => 'items', 'type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'max' => 8, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                    ['key' => 'link', 'type' => 'link', 'label' => 'Links to'],
                ], 'default' => $items],
            ],
        ];
    };

    $conditionIcons = [
        'bite'     => 'M4 9h16M6 9v3a6 6 0 0 0 12 0V9 M8 15h8',
        'crowding' => 'M5 7v10M9.5 6v12M14.5 6v12M19 7v10',
        'gap'      => 'M6 6v12M18 6v12 M9 12h6',
        'cross'    => 'M5 8l14 8M5 16l14-8',
        'open'     => 'M4 8h16M4 16h16',
        'smile'    => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z M8 14a4 4 0 0 0 8 0',
    ];

    /* ---------- the registry ---------- */

    $templates = [];

    /* ============================================================
       SERVICE — Simple page
       ============================================================ */
    $templates['service-simple'] = [
        'type'    => 'service',
        'name'    => 'Simple Page',
        'tagline' => 'A navy header and a written page. Best for short treatment or information pages.',
        'family'  => 'simple',
        'wrap'    => 'ig-home',
        'css'     => ['home.css', 'page.css', 'blog.css'],
        'js'      => ['home.js'],
        'shape'   => ['band', 'text', 'text', 'cards'],
        'sections' => [
            'hero' => [
                'label'  => 'Header',
                'locked' => true,
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label above the heading', 'default' => 'Orthodontic Treatment'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading (H1)', 'default' => 'A Smile Is *Timeless.*'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'Tell visitors in a sentence or two what this page is about.'],
                ],
            ],
            'body' => [
                'label'  => 'Page content',
                'help'   => 'Write the page the same way you write a blog post. Use Heading 2 for each section.',
                'locked' => true,
                'fields' => [
                    ['key' => 'note', 'type' => 'text', 'label' => 'Highlighted notice (optional)', 'help' => 'Shown in an orange box above the content.'],
                    ['key' => 'html', 'type' => 'richtext', 'label' => 'Content', 'default' => '<h2>About this treatment</h2><p>Replace this with your own words.</p>'],
                ],
            ],
            'links' => [
                'label'  => 'Link cards',
                'help'   => 'Cards linking to other pages on this site.',
                'fields' => [
                    ['key' => 'items', 'type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'max' => 12, 'fields' => [
                        ['key' => 'label', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                        ['key' => 'link', 'type' => 'link', 'label' => 'Links to'],
                    ], 'default' => []],
                ],
            ],
            'offices' => [
                'label' => 'Office list',
                'help'  => 'The searchable list of every office, as it appears on the Locations page.',
                'off'   => true,
                'fields' => [],
            ],
            'consult' => $consultSection + ['locked' => true],
        ],
    ];

    /* ============================================================
       SERVICE — Treatment page (the office-page design)
       ============================================================ */
    $templates['service-classic'] = [
        'type'    => 'service',
        'name'    => 'Treatment Page',
        'tagline' => 'The full page design: photo header, highlight cards, offers, treatment cards and FAQ.',
        'family'  => 'classic',
        'wrap'    => 'ig-locpage',
        'css'     => ['location.css'],
        'js'      => ['location.js'],
        'shape'   => ['hero', 'cards', 'cards', 'band', 'faq'],
        'sections' => [
            'hero' => [
                'label'  => 'Header',
                'locked' => true,
                'fields' => [
                    ['key' => 'pill', 'type' => 'text', 'label' => 'Small label above the heading', 'default' => 'Braces And Clear Aligners In Michigan'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading (H1)', 'default' => 'Your Best *Smile* Starts Here'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Text under the heading', 'default' => 'Personalised treatment plans from a specialist orthodontist, with flexible payment options.'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Background photo', 'default' => '/assets/img/invisalign-braces-scaled.jpg'],
                ],
            ],
            'intro' => [
                'label' => 'Introduction',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'The Orthodontist Families Recommend'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'How We *Stand Out*'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'default' => 'Our experienced orthodontists create personalised treatment plans that deliver safe, effective results, with advanced training and years of patient care behind every decision.'],
                    ['key' => 'text2', 'type' => 'textarea', 'label' => 'Second paragraph (optional)'],
                ],
            ],
            'highlights' => [
                'label' => 'Highlight cards',
                'help'  => 'Four photo cards that open the booking popup.',
                'fields' => [
                    ['key' => 'items', 'type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'max' => 8, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                    ], 'default' => [
                        ['title' => 'Treatment Options', 'text' => 'Traditional metal braces, Invisalign®, 3M clarity aligners and other modern solutions, from local orthodontists who put your needs first.', 'image' => '/assets/img/01-image.webp'],
                        ['title' => 'Experienced Care', 'text' => 'Our orthodontists have guided thousands of patients from first consultation to finished smile. Every plan is designed and monitored by a specialist.', 'image' => '/assets/img/02-image.webp'],
                        ['title' => 'Affordable Solutions', 'text' => 'Interest-free in-house payment plans, clear pricing and no unexpected costs. We accept most major insurance plans and verify your benefits first.', 'image' => '/assets/img/03-image.webp'],
                        ['title' => 'Care For All Ages', 'text' => 'From a child’s first evaluation through teen and adult treatment, we tailor care to every stage of life.', 'image' => '/assets/img/img1-1.jpg'],
                    ]],
                ],
            ],
            'offers' => [
                'label' => 'Discounts and offers',
                'help'  => 'Check the wording of every offer before publishing.',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Affordable Orthodontic Care'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'Discounts And *Offers*'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'With flexible payment plans, clear pricing and no hidden fees, you can trust us to deliver affordable braces, clear aligners and Invisalign® without compromising on quality.'],
                    ['key' => 'items', 'type' => 'list', 'label' => 'Offers', 'item' => 'Offer', 'max' => 6, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Offer', 'help' => 'Use | where the line should break, for example: Braces from|$99*/Month'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                        ['key' => 'terms', 'type' => 'text', 'label' => 'Small print'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                    ], 'default' => [
                        ['title' => 'Braces from|$99*/Month', 'text' => 'Braces from just $99* per month. Offer terms and eligibility apply.', 'terms' => 'Terms and Conditions', 'image' => '/assets/img/happy-family.jpg'],
                        ['title' => 'Clear Aligners from|$185*/Month', 'text' => 'Clear aligners from $185* per month. Offer terms and eligibility apply.', 'terms' => 'Terms and Conditions', 'image' => '/assets/img/hero2.png'],
                        ['title' => 'Interest-Free|Payment Plans', 'text' => 'Enjoy 0% APR with Ignite Orthodontics’ in-house financing plans.', 'terms' => 'Terms and Conditions', 'image' => '/assets/img/03-image.webp'],
                    ]],
                ],
            ],
            'treatments' => $treatmentsSection('Best Orthodontic *Treatment*', $treatmentItems),
            'ages' => [
                'label' => 'Care for every age',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Smile With Confidence At Every Stage Of Life'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'Family-Friendly *Orthodontics*'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'Personalised care for children, teens and adults — because the right treatment depends on where you are in life.'],
                    ['key' => 'items', 'type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'max' => 6, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                        ['key' => 'image', 'type' => 'image', 'label' => 'Photo'],
                        ['key' => 'button', 'type' => 'text', 'label' => 'Button', 'default' => 'Schedule Now'],
                    ], 'default' => [
                        ['title' => 'Kids', 'text' => 'We focus on early intervention, guiding growth so later treatment is shorter and simpler.', 'image' => '/assets/img/01-image.webp', 'button' => 'Schedule Now'],
                        ['title' => 'Teens', 'text' => 'Choose from metal braces in your own colours or virtually invisible clear aligners.', 'image' => '/assets/img/02-image.webp', 'button' => 'Schedule Now'],
                        ['title' => 'Adults', 'text' => 'It is never too late. Discreet treatment options fit around work and everyday life.', 'image' => '/assets/img/03-image.webp', 'button' => 'Schedule Now'],
                    ]],
                ],
            ],
            'cta_mid' => [
                'label' => 'Call-to-action band',
                'fields' => [
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'Experience the Best in Orthodontic Care — *Schedule A No-Cost Consultation Now!*'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'default' => 'Your first visit includes a full exam, x-rays and a personalised treatment plan, at no charge and with no obligation.'],
                ],
            ],
            'consult' => $consultSection + ['locked' => true],
            'conditions' => [
                'label' => 'Conditions we treat',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Orthodontic Conditions & Solutions'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'Personalised Orthodontic *Treatment*'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'Do not let misaligned teeth or jaws affect your oral health and confidence. These are the conditions we correct most often.'],
                    ['key' => 'items', 'type' => 'list', 'label' => 'Conditions', 'item' => 'Condition', 'max' => 10, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                        ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => [
                            'bite' => 'Bite', 'crowding' => 'Crowded teeth', 'gap' => 'Gap', 'cross' => 'Crossbite', 'open' => 'Open bite', 'smile' => 'Smile',
                        ]],
                    ], 'default' => [
                        ['title' => 'Overbite', 'text' => 'When the top teeth extend too far over the lower teeth.', 'icon' => 'bite'],
                        ['title' => 'Crowding', 'text' => 'Overlapping teeth that are difficult to clean properly.', 'icon' => 'crowding'],
                        ['title' => 'Gapped Teeth', 'text' => 'Significant spacing between teeth that can affect the bite.', 'icon' => 'gap'],
                        ['title' => 'Crossbite', 'text' => 'Also known as an underbite, where the jaws do not align.', 'icon' => 'cross'],
                        ['title' => 'Open Bite', 'text' => 'When the top and bottom teeth do not meet on closing.', 'icon' => 'open'],
                    ]],
                ],
            ],
            'cta_end' => [
                'label' => 'Closing call-to-action',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'We Can’t Wait To See You Soon!'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'Your Trusted *Orthodontists* Are Ready to Help'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'default' => 'Our team believes in welcoming every patient with the highest standard of orthodontic care — from your first consultation to the day your braces come off.'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/happy-family.jpg'],
                ],
            ],
            'faq' => $faqSection([
                ['q' => 'How long does braces treatment usually last?', 'a' => 'Most cases run between 12 and 24 months. Your orthodontist will give you a specific estimate at your consultation, once they have seen your x-rays and scans.'],
                ['q' => 'Is there an age limit for orthodontic treatment?', 'a' => 'No. Healthy teeth can be moved at any age. We treat children from around seven through to adults in their sixties and beyond.'],
                ['q' => 'Do you offer flexible payment options?', 'a' => 'Yes. We offer interest-free in-house payment plans with no hidden fees, and we accept most major insurance plans. We will verify your benefits before treatment starts.'],
            ]),
        ],
    ];

    /* ============================================================
       SERVICE — Spotlight (the kids-page design)
       ============================================================ */
    $templates['service-spotlight'] = [
        'type'    => 'service',
        'name'    => 'Spotlight Page',
        'tagline' => 'Bold split header, checklist, option cards, a step-by-step section and a dark payment band.',
        'family'  => 'spotlight',
        'wrap'    => 'kp',
        'css'     => ['kids.css'],
        'js'      => ['kids.js'],
        'shape'   => ['split', 'split', 'cards', 'dark', 'faq'],
        'sections' => [
            'hero' => [
                'label'  => 'Header',
                'locked' => true,
                'fields' => [
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading (H1)', 'default' => 'Creating Healthy, Confident *Smiles for Kids* & Teens'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Text under the heading', 'default' => 'Gentle, fun, and stress-free orthodontic care designed specifically for children. Guide early jaw growth, fix crowding, and build a lifetime of confidence!'],
                    ['key' => 'button', 'type' => 'text', 'label' => 'Button', 'default' => 'Book Free Consultation'],
                    ['key' => 'points', 'type' => 'lines', 'label' => 'Small points beside the buttons', 'help' => 'One per line.', 'default' => "0% Interest Payment Plans\nMost Insurances Accepted"],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/istockphoto-1202583861-612x612-1.webp', 'alt_default' => 'Kids Braces Smile Consultation'],
                ],
            ],
            'highlight' => [
                'label' => 'Highlight and checklist',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Early Growth Guidance'],
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'default' => 'Why the AAO Recommends a Checkup by Age 7'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'By age 7, a child has a mix of baby teeth and adult teeth, allowing orthodontists to catch potential jaw and bite issues before they become complex problems.'],
                    ['key' => 'card_title', 'type' => 'text', 'label' => 'Card title', 'default' => 'Benefits of Interceptive Treatment'],
                    ['key' => 'card_text', 'type' => 'textarea', 'label' => 'Card text', 'default' => 'Early treatment (Phase 1) works with your child’s natural growth spurts to achieve optimal facial symmetry and jaw expansion.'],
                    ['key' => 'checklist', 'type' => 'lines', 'label' => 'Checklist', 'help' => 'One benefit per line.', 'default' => "Guides jaw growth for proper facial structure\nCreates room for crowded, emerging adult teeth\nFixes thumb-sucking and tongue-thrust habits\nReduces the need to extract permanent teeth later\nSimplifies future comprehensive treatment (Phase 2)"],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/shutterstock_2331223617-min.jpg', 'alt_default' => 'Child orthodontic examination'],
                    ['key' => 'boxes', 'type' => 'list', 'label' => 'Small boxes under the photo', 'item' => 'Box', 'max' => 4, 'fields' => [
                        ['key' => 'tag', 'type' => 'text', 'label' => 'Label'],
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                    ], 'default' => [
                        ['tag' => 'Phase 1 (Ages 7–10)', 'title' => 'Preventative Guidance', 'text' => 'Focuses on jaw expansion and space maintenance while your child is actively growing.'],
                        ['tag' => 'Phase 2 (Ages 11+)', 'title' => 'Comprehensive Alignment', 'text' => 'Full braces or clear aligners once permanent adult teeth erupt to finalize alignment.'],
                    ]],
                ],
            ],
            'options' => [
                'label' => 'Option cards',
                'help'  => 'Cards alternate between white and orange automatically.',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Tailored Treatments'],
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'default' => 'Orthodontic Options Designed for Kids'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'Every child’s smile is unique. We offer modern, comfortable treatment options that match your child’s lifestyle and aesthetic preferences.'],
                    ['key' => 'items', 'type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'max' => 8, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                        ['key' => 'tag', 'type' => 'text', 'label' => 'Tag at the bottom'],
                    ], 'default' => [
                        ['title' => 'Metal Braces', 'text' => 'Durable, precise, and fun! Kids love choosing custom colored elastics at every appointment.', 'tag' => 'Most Popular for Kids'],
                        ['title' => 'Clear Ceramic Braces', 'text' => 'Blends naturally with tooth color for a discreet look that image-conscious teens prefer.', 'tag' => 'Discreet & Aesthetic'],
                        ['title' => 'Invisalign® First', 'text' => 'Removable, clear aligners engineered specifically for growing children with mixed baby and adult teeth.', 'tag' => 'No Food Restrictions'],
                        ['title' => 'Palatal Expanders', 'text' => 'Gentle appliances that widen narrow upper jaws, alleviating severe crowding and improving breathing.', 'tag' => 'Early Growth Helper'],
                    ]],
                ],
            ],
            'steps' => [
                'label' => 'Numbered steps',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Parent & Kid Friendly'],
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'default' => 'What to Expect: A Stress-Free Journey'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'We make orthodontic appointments welcoming, engaging, and fun for your child from day one.'],
                    ['key' => 'items', 'type' => 'list', 'label' => 'Steps', 'item' => 'Step', 'max' => 8, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                    ], 'default' => [
                        ['title' => 'Warm Welcome', 'text' => 'Friendly staff, comfortable environment, and zero pressure. We explain everything in kid-friendly terms.'],
                        ['title' => '3D Digital Scans', 'text' => 'No gooey, uncomfortable mold impressions! Our fast 3D digital scanner creates a precise digital model of teeth.'],
                        ['title' => 'Personalized Plan', 'text' => 'Our orthodontists design a tailored roadmap with clear timelines, transparent pricing, and flexible payment plans.'],
                        ['title' => 'Pick Color Bands!', 'text' => 'Your child gets to personalize their braces with their favorite sports team, holiday, or school colors.'],
                    ]],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/istockphoto-2166449878-612x612-1.webp', 'alt_default' => 'Kids orthodontic care experience'],
                ],
            ],
            'afford' => [
                'label' => 'Payment band (dark)',
                'fields' => [
                    ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Financial Peace of Mind'],
                    ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'default' => 'Making Kids Braces Affordable for Every Family'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'default' => 'We believe every child deserves a healthy smile. We work directly with your insurance and offer low monthly payment options.'],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/istockphoto-1367329991-612x612-1.webp', 'alt_default' => 'Affordable orthodontics for kids'],
                    ['key' => 'items', 'type' => 'list', 'label' => 'Boxes', 'item' => 'Box', 'max' => 6, 'fields' => [
                        ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                        ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                    ], 'default' => [
                        ['title' => '$0 Down Options', 'text' => 'Flexible low monthly payment plans with zero interest options to fit your family budget.'],
                        ['title' => 'Insurance Maximized', 'text' => 'We accept most dental PPO plans and file claims on your behalf to maximize your orthodontic benefits.'],
                        ['title' => 'Free Consultation', 'text' => 'Your child\'s first consultation, X-rays, and 3D scans are 100% complimentary with no obligation.'],
                    ]],
                    ['key' => 'button', 'type' => 'text', 'label' => 'Button', 'default' => 'Check Financial Options'],
                ],
            ],
            'faq' => $faqSection([
                ['q' => 'At what age should my child first see an orthodontist?', 'a' => 'The American Association of Orthodontists (AAO) recommends that every child have their first orthodontic evaluation by age 7. At this stage, early jaw problems and crowding can be identified and corrected before permanent teeth fully erupt.'],
                ['q' => 'Does early treatment (Phase 1) mean my child will not need braces later?', 'a' => 'Phase 1 prepares the jaw and creates necessary space for permanent teeth to erupt properly. While many children still require Phase 2 (full braces or aligners) in their teen years, Phase 1 makes Phase 2 significantly shorter, easier, and less invasive.'],
                ['q' => 'Do braces hurt when first put on?', 'a' => 'Putting braces on does not hurt at all. Your child may feel mild soreness or pressure for a few days after adjustments as teeth begin moving. Over-the-counter pain relievers and soft foods easily keep them comfortable.'],
                ['q' => 'Can my child still play sports or musical instruments?', 'a' => 'Absolutely. Children with braces can participate in all sports and play wind or brass instruments. We recommend wearing a custom orthodontic mouthguard during sports to protect their teeth and lips.'],
                ['q' => 'How much do braces for kids cost?', 'a' => 'Treatment costs vary based on your child\'s specific alignment needs and treatment duration. We offer free consultations, accept dental insurance, and provide flexible 0% interest monthly payment options.'],
            ], 'Frequently Asked Questions'),
            'consult' => $consultSection,
        ],
    ];

    /* ============================================================
       LOCATION — Full office page
       ============================================================ */
    $office = [
        'label'  => 'Office details',
        'help'   => 'Used on this page, in the menu, the office list, the footer and the booking popup.',
        'locked' => true,
        'fields' => [
            ['key' => 'street', 'type' => 'text', 'label' => 'Street address'],
            ['key' => 'city', 'type' => 'text', 'label' => 'City'],
            ['key' => 'state', 'type' => 'text', 'label' => 'State', 'default' => 'MI'],
            ['key' => 'zip', 'type' => 'text', 'label' => 'ZIP code'],
            ['key' => 'phone', 'type' => 'text', 'label' => 'Phone number', 'default' => '(xxx) xxx-xxxx', 'help' => 'Shown as typed. A real 10-digit number also becomes a tap-to-call link.'],
            ['key' => 'email', 'type' => 'text', 'label' => 'Email address'],
            ['key' => 'hours', 'type' => 'lines', 'label' => 'Opening hours (optional)', 'help' => 'One per line, for example: Monday 9:00 AM – 5:00 PM'],
        ],
    ];

    $contact = [
        'label' => 'Address and photo',
        'fields' => [
            ['key' => 'kicker', 'type' => 'text', 'label' => 'Small label', 'default' => 'Our Location'],
            ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading', 'default' => 'Ignite Orthodontics *{office}*'],
            ['key' => 'note', 'type' => 'text', 'label' => 'Text under the details', 'default' => 'Call the office for current hours and appointment availability.'],
            ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/02-image.webp', 'alt_default' => 'Smiling patient at Ignite Orthodontics {office}'],
        ],
    ];

    $locationHero = [
        'label'  => 'Header',
        'locked' => true,
        'fields' => [
            ['key' => 'pill', 'type' => 'text', 'label' => 'Small label above the heading', 'default' => 'Transform Your Smile With The Best Orthodontists in {office}'],
            ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading (H1)', 'default' => 'Ignite Orthodontics *{office}*'],
            ['key' => 'image', 'type' => 'image', 'label' => 'Background photo', 'default' => '/assets/img/invisalign-braces-scaled.jpg'],
        ],
    ];

    $classic = $templates['service-classic']['sections'];

    $templates['location-full'] = [
        'type'    => 'location',
        'name'    => 'Full Office Page',
        'tagline' => 'Every section: photo header with the address, highlights, offers, treatments, contact details and FAQ.',
        'family'  => 'classic',
        'wrap'    => 'ig-locpage',
        'css'     => ['location.css'],
        'js'      => ['location.js'],
        'shape'   => ['hero', 'cards', 'cards', 'band', 'faq'],
        'sections' => [
            'office'     => $office,
            'hero'       => $locationHero,
            'intro'      => array_replace_recursive($classic['intro'], ['fields' => [
                ['default' => 'The Orthodontist In {office} Families Recommend'],
                ['default' => 'How We *Stand Out*'],
                ['default' => 'Our experienced orthodontist in {office} specializes in Invisalign®, 3M™ Clear Aligners, and braces. With advanced training and years of patient care, we create personalized treatment plans that deliver safe and effective results.'],
                ['default' => 'We are proud to be recognized in the {office} community for our expertise, transparency, and compassionate care, helping families achieve healthier, more confident smiles.'],
            ]]),
            'highlights' => $classic['highlights'],
            'offers'     => array_replace_recursive($classic['offers'], ['fields' => [
                ['default' => 'Affordable Orthodontist In {office}'],
                [], [], [],
            ]]),
            'treatments' => $treatmentsSection('Best Orthodontic Treatment In *{office}*', $treatmentItems),
            'ages'       => array_replace_recursive($classic['ages'], ['fields' => [
                [], ['default' => 'Family-Friendly Orthodontist in *{office}*'], [], [],
            ]]),
            'cta_mid'    => array_replace_recursive($classic['cta_mid'], ['fields' => [
                ['default' => 'Experience the Best in Orthodontic Care in {office} — *Schedule A No-Cost Consultation Now!*'],
                [],
            ]]),
            'consult'    => $consultSection + ['locked' => true],
            'contact'    => $contact,
            'conditions' => array_replace_recursive($classic['conditions'], ['fields' => [
                [], ['default' => 'Personalised Orthodontic Treatment in *{office}*'], [], [],
            ]]),
            'cta_end'    => array_replace_recursive($classic['cta_end'], ['fields' => [
                [],
                ['default' => 'Your Trusted Orthodontists in *{office}* Are Ready to Help'],
                ['default' => 'Our {office} team believes in welcoming every patient with the highest standard of orthodontic care — from your first consultation to the day your braces come off.'],
                [],
            ]]),
            'faq' => $faqSection([
                ['q' => 'How do I schedule an appointment at Ignite Orthodontics in {office}?', 'a' => 'Call our {office} office or use the booking button on this page. We will confirm your appointment and send everything you need beforehand.'],
                ['q' => 'What kind of follow-up care is available?', 'a' => 'Regular adjustment visits are built into every treatment plan, and retainers are fitted once active treatment ends. If something breaks between visits, call us and we will get you seen.'],
                ['q' => 'Does Ignite Orthodontics in {office} offer flexible payment options?', 'a' => 'Yes. We offer interest-free in-house payment plans with no hidden fees, and we accept most major insurance plans. We will verify your benefits before treatment starts.'],
                ['q' => 'Can an orthodontist fix a bad bite?', 'a' => 'Yes. Overbite, underbite, crossbite and open bite are all correctable. Which approach we recommend depends on your age and how the jaws are positioned, which we will assess at your consultation.'],
                ['q' => 'Is there an age limit for orthodontic treatment?', 'a' => 'No. Healthy teeth can be moved at any age. We treat children from around seven through to adults in their sixties and beyond.'],
                ['q' => 'How long does braces treatment usually last?', 'a' => 'Most cases run between 12 and 24 months. Your orthodontist will give you a specific estimate at your consultation, once they have seen your x-rays and scans.'],
                ['q' => 'What is the cost of adult braces in {office}?', 'a' => 'Cost depends on the treatment type and how long you are in braces. We will give you an exact figure — with the monthly payment broken out — at your no-cost consultation.'],
            ]),
        ],
    ];

    /* ============================================================
       LOCATION — Short office page
       ============================================================ */
    $templates['location-compact'] = [
        'type'    => 'location',
        'name'    => 'Short Office Page',
        'tagline' => 'A lighter office page: header, introduction, treatments, contact details and FAQ.',
        'family'  => 'classic',
        'wrap'    => 'ig-locpage',
        'css'     => ['location.css'],
        'js'      => ['location.js'],
        'shape'   => ['hero', 'text', 'cards', 'band'],
        'sections' => [
            'office'     => $office,
            'hero'       => $locationHero,
            'intro'      => $templates['location-full']['sections']['intro'],
            'treatments' => $templates['location-full']['sections']['treatments'],
            'cta_mid'    => $templates['location-full']['sections']['cta_mid'],
            'consult'    => $consultSection + ['locked' => true],
            'contact'    => $contact,
            'faq'        => $templates['location-full']['sections']['faq'],
        ],
    ];

    /* ============================================================
       SERVICE — Treatment guide (long-form, built from blocks)
       ============================================================ */
    $templates['service-guide'] = [
        'type'    => 'service',
        'name'    => 'Treatment Guide',
        'tagline' => 'A long-form treatment page built from blocks you add and reorder: feature cards, steps, comparison table, pricing, reviews and FAQ.',
        'family'  => 'guide',
        'wrap'    => 'gd',
        'css'     => ['home.css', 'guide.css'],
        'js'      => ['home.js'],
        'shape'   => ['split', 'cards', 'text', 'dark', 'faq'],
        'sections' => [
            'hero' => [
                'label'  => 'Header',
                'locked' => true,
                'fields' => [
                    ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Small label above the heading', 'default' => 'Orthodontic Treatment'],
                    ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading (H1)', 'default' => 'A Straighter Smile, *Planned Around You*'],
                    ['key' => 'lead', 'type' => 'textarea', 'label' => 'Intro text', 'help' => 'Each line becomes its own paragraph.', 'default' => 'Tell visitors what this treatment is and who it is for.'],
                    ['key' => 'price', 'type' => 'text', 'label' => 'Price badge (optional)', 'help' => 'For example: From $99/month*', 'default' => ''],
                    ['key' => 'price_note', 'type' => 'text', 'label' => 'Price small print (optional)'],
                    ['key' => 'button', 'type' => 'text', 'label' => 'Button', 'default' => 'Schedule Your Free Consultation'],
                    ['key' => 'points', 'type' => 'lines', 'label' => 'Short points under the buttons', 'help' => 'One per line.', 'default' => "Insurance accepted\nFlexible financing\nNo referral required"],
                    ['key' => 'image', 'type' => 'image', 'label' => 'Photo', 'default' => '/assets/img/IMG_20260814_125146.jpg'],
                ],
            ],
            'content' => [
                'label'  => 'Page blocks',
                'help'   => 'The body of the page. Add blocks, open one to edit it, and use the arrows to change the order.',
                'locked' => true,
                'fields' => [
                    ['key' => 'blocks', 'type' => 'blocks', 'label' => 'Blocks', 'types' => guide_block_types(), 'default' => [
                        ['_type' => 'text', 'eyebrow' => 'About This Treatment', 'heading' => 'What Is *This Treatment?*', 'intro' => "Explain the treatment in plain words.\nKeep each paragraph to one or two sentences.", 'list' => '', 'image' => '', 'tone' => 'white'],
                        ['_type' => 'steps', 'eyebrow' => 'How It Works', 'heading' => 'Your Treatment, *Step by Step*', 'items' => [
                            ['title' => 'Free Consultation', 'text' => 'We evaluate your teeth and bite and talk about your goals.'],
                            ['title' => 'Treatment Plan', 'text' => 'Your orthodontist explains your options, timeline and cost.'],
                            ['title' => 'Your New Smile', 'text' => 'Retainers help keep your results in place.'],
                        ], 'tone' => 'tint'],
                        ['_type' => 'faq', 'eyebrow' => 'Questions', 'heading' => 'Frequently Asked *Questions*', 'items' => [
                            ['q' => 'How long does treatment take?', 'a' => 'Treatment time varies. Your orthodontist will give you an estimate at your consultation.'],
                        ], 'tone' => 'white'],
                    ]],
                ],
            ],
            'offices' => [
                'label' => 'Office list',
                'help'  => 'The searchable list of every office, as it appears on the Locations page.',
                'fields' => [],
            ],
            'consult' => $consultSection,
        ],
    ];

    foreach ($templates as $key => &$template) {
        $template['key']   = $key;
        $template['icons'] = $conditionIcons;
    }
    unset($template);

    return $templates;
}

function template(?string $key): ?array
{
    return templates()[$key ?? ''] ?? null;
}

/** Templates offered for a page type ('service' or 'location'). */
function templates_for(string $type): array
{
    return array_filter(templates(), static fn(array $t): bool => $t['type'] === $type);
}

function template_default(string $type): string
{
    return $type === 'location' ? 'location-full' : 'service-simple';
}

/** Every field of a section, including the alt-text field each image adds. */
function template_section_fields(array $section): array
{
    return $section['fields'] ?? [];
}
