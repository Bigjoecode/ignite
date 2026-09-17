<?php
// Treatment pages written for Ignite Orthodontics (September 2026), for the
// "Treatment Guide" layout. Loaded by app/cli/import-pages.php, which saves each
// page through the same cleaning as the admin editor. After import the pages
// are edited in /admin/pages/ and this file is not read again.
//
// Editorial changes from the source documents: "Ignite Orthodontic" is written
// "Ignite Orthodontics"; Invisalign pricing is $185/month (confirmed by the
// practice); closed offices are not listed; review sections that only held
// "[Insert verified review]" placeholders are left out; a lingual braces panel
// was added to the braces page at the practice's request.

$lines = static fn(string ...$items): string => implode("\n", $items);
$steps = static fn(array ...$pairs): array => array_map(static fn(array $p): array => ['title' => $p[0], 'text' => $p[1]], $pairs);
$cards = static fn(array ...$rows): array => array_map(static fn(array $p): array => ['icon' => $p[0], 'title' => $p[1], 'text' => $p[2]], $rows);
$faq   = static fn(array ...$pairs): array => array_map(static fn(array $p): array => ['q' => $p[0], 'a' => $p[1]], $pairs);
$rows  = static fn(array ...$rows): array => array_map(static fn(array $p): array => ['label' => $p[0], 'values' => implode("\n", array_slice($p, 1))], $rows);
$pay   = static fn(string $insurance, string $financing, string $pocket): array => [
    ['icon' => 'shield', 'title' => 'Insurance', 'text' => $insurance],
    ['icon' => 'wallet', 'title' => 'Financing', 'text' => $financing],
    ['icon' => 'check', 'title' => 'Out-of-Pocket', 'text' => $pocket],
];

$free = 'Schedule Your Free Consultation';

return [
    'rewrite_links' => [
        '/ceramic-braces/' => '/types-of-braces/ceramic-braces/',
        '/types-of-braces/#traditional-metal-braces' => '/types-of-braces/traditional-braces/',
    ],

    'pages' => [

        /* ================================================================
           CLEAR ALIGNERS
           ================================================================ */
        [
            'slug' => 'clear-aligners',
            'title' => 'Clear Aligners',
            'seo_title' => 'Clear Aligners in Michigan | Ignite Orthodontics',
            'description' => 'Clear aligners at Ignite Orthodontics: removable, discreet trays planned and monitored by a board-certified orthodontist. Book a no-cost consultation.',
            'hero' => [
                'eyebrow' => 'Clear Aligners',
                'heading' => 'A More Discreet Way to *Straighten Your Smile*',
                'lead' => $lines(
                    "Clear aligners use a series of custom-made, removable trays to gradually move your teeth. They're designed to fit closely over your teeth, making them less noticeable than traditional braces.",
                    'At Ignite Orthodontics, your clear aligner treatment is planned and monitored by a board-certified orthodontist based on your teeth, bite, and individual orthodontic needs.'
                ),
                'button' => 'Schedule a Consultation',
                'points' => $lines('Board-certified orthodontist', 'No-cost consultations', 'Flexible payment options'),
                'image' => '/assets/img/IMG_20260814_125204.jpg',
                'image_alt' => 'Patient putting in a clear aligner',
            ],
            'blocks' => [
                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'The Basics', 'heading' => 'What Are *Clear Aligners?*',
                    'intro' => $lines(
                        'Clear aligners are removable orthodontic trays made to fit your teeth.',
                        'Instead of brackets and wires, you wear a series of aligners. Each set is designed to make specific adjustments to your teeth. As you move through the series, your teeth gradually shift toward their planned positions.',
                        'Because aligners are removable, you can take them out for eating, drinking, brushing, and flossing.',
                        "They're also designed to be discreet, which makes them a popular option for adults and teens who prefer a less noticeable form of orthodontic treatment."
                    ),
                    'image' => '/assets/img/home-page-hero-image-adults-orth.jpg', 'image_alt' => 'Smiling patient holding clear aligners', 'image_side' => 'right'],

                ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'How It Works', 'heading' => 'How Do *Clear Aligners Work?*',
                    'intro' => 'Clear aligner treatment typically follows several stages:',
                    'items' => $steps(
                        ['Orthodontic Evaluation', 'Your orthodontist examines your teeth and bite to determine whether clear aligners are appropriate for your needs.'],
                        ['Treatment Planning', 'Your orthodontist develops a treatment plan based on the movements needed to improve your alignment.'],
                        ['Custom Aligners', 'Your aligners are made specifically for your teeth.'],
                        ['Wearing Your Aligners', "You'll wear each set according to your orthodontist's instructions before moving to the next set."],
                        ['Progress Checks', 'Regular appointments allow your orthodontist to monitor your progress and make sure treatment is proceeding as planned.'],
                        ['Retention', "After active treatment, you'll generally need retainers to help maintain your teeth in their new positions."]
                    )],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'What They Treat', 'heading' => 'What Can *Clear Aligners Treat?*',
                    'intro' => $lines('Clear aligners can address many common orthodontic concerns, depending on the individual case.', 'These may include:'),
                    'list' => $lines('Crowded teeth', 'Gapped teeth', 'Some overbites', 'Some underbites', 'Some crossbites', 'Certain alignment problems'),
                    'list_style' => 'check',
                    'outro' => $lines(
                        'Not every orthodontic problem can be treated with clear aligners. The complexity of your case and the movements required will determine whether aligners are an appropriate option.',
                        "That's why an orthodontic evaluation matters."
                    ),
                    'button' => 'Schedule a Consultation'],

                ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'Adults & Teens', 'heading' => 'Clear Aligners for *Every Stage of Life*',
                    'items' => [
                        ['label' => 'Adults', 'title' => 'Clear Aligners for Adults', 'text' => $lines(
                            'Clear aligners can be particularly appealing to adults who want a more discreet orthodontic treatment option.',
                            'You can remove them when eating and brush and floss normally without brackets and wires getting in the way.',
                            "They're also less noticeable during work meetings, social events, photographs, and everyday interactions.",
                            "However, clear aligners require responsibility. Because they're removable, you need to wear them as directed and keep track of them.",
                            "If you don't want to manage removable trays, traditional braces may be a better fit for your lifestyle."
                        ), 'link' => '/types-of-braces/traditional-braces/', 'link_label' => 'Learn about traditional braces'],
                        ['label' => 'Teens', 'title' => 'Clear Aligners for Teens', 'text' => $lines(
                            'Clear aligners can also be an option for some teenagers.',
                            "The important consideration is whether your teen's orthodontic needs can be appropriately treated with aligners and whether they're ready to follow the treatment instructions.",
                            "Your orthodontist will consider the alignment and bite concerns involved, as well as your teen's ability to wear and care for the aligners consistently.",
                            'Traditional braces remain another option for teens who need a fixed treatment approach.'
                        ), 'link' => '/braces-for-teens/', 'link_label' => 'Braces for teens'],
                    ]],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Benefits', 'heading' => 'Why Choose *Clear Aligners?*',
                    'intro' => 'Clear aligners offer several practical differences from traditional braces.',
                    'items' => $cards(
                        ['refresh', 'Removable', 'You can take your aligners out when eating, brushing, and flossing.'],
                        ['eye', 'Discreet', 'The clear trays are designed to be less noticeable than traditional metal brackets and wires.'],
                        ['sparkle', 'No Brackets or Wires', 'There are no traditional brackets attached to the front of your teeth.'],
                        ['brush', 'Easier Oral Hygiene', 'Removing your aligners allows you to brush and floss your teeth normally.'],
                        ['heart', 'Fits an Active Lifestyle', 'Aligners can be removed for meals and certain activities when instructed by your orthodontic team.']
                    ),
                    'note' => "These benefits don't mean clear aligners are automatically better than braces. They're simply different. The right treatment depends on your orthodontic needs and circumstances."],

                ['_type' => 'compare', 'tone' => 'tint', 'eyebrow' => 'Compare Your Options', 'heading' => 'Clear Aligners vs. *Traditional Braces*',
                    'intro' => 'Both options can straighten teeth, but they work differently.',
                    'columns' => $lines('Clear Aligners', 'Traditional Braces'),
                    'rows' => $rows(
                        ['', 'Removable trays', 'Fixed brackets and wires'],
                        ['', 'Less noticeable', 'More visible'],
                        ['', 'Removed for eating and oral hygiene', 'Remain in place during treatment'],
                        ['', 'Require consistent wear', "Don't depend on remembering to put them in"],
                        ['', 'Appropriate for certain orthodontic cases', 'Can treat a wide range of orthodontic problems']
                    ),
                    'note' => 'If both options are appropriate for your case, your orthodontist can help you compare them based on treatment needs, lifestyle, appearance, and other priorities.'],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Everyday Life', 'heading' => 'What Is Daily Life With *Clear Aligners* Like?',
                    'intro' => $lines("Clear aligners are designed to fit into your normal routine, but they do require consistency.", "You'll need to:"),
                    'list' => $lines(
                        "Wear your aligners according to your orthodontist's instructions",
                        'Remove them when eating if instructed',
                        'Brush and floss your teeth regularly',
                        'Clean your aligners as directed',
                        "Keep your aligners safe when they're not in your mouth",
                        'Move through your aligners according to your treatment plan',
                        'Attend scheduled progress appointments'
                    ),
                    'list_style' => 'check',
                    'outro' => 'The convenience of removable aligners comes with a responsibility: you have to wear them consistently for treatment to stay on track.'],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Aligner Care', 'heading' => 'Caring for Your *Clear Aligners*',
                    'intro' => $lines(
                        'Your aligners need to stay clean while you\'re wearing them.',
                        "Follow your orthodontist's instructions for cleaning and storing them. When you remove your aligners, keep them in their case rather than placing them loosely on a table, napkin, or other surface where they can easily be misplaced.",
                        'Good oral hygiene is important throughout treatment. Brush and floss your teeth regularly, and continue routine dental care with your general dentist.'
                    ),
                    'image' => '/assets/img/hero2.png', 'image_alt' => 'Smiling patient holding a clear aligner', 'image_side' => 'left'],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Cost', 'heading' => 'How Much Do *Clear Aligners Cost?*',
                    'intro' => $lines('The cost of clear aligner treatment varies from patient to patient.', 'Factors can include:'),
                    'list' => $lines('Complexity of your orthodontic case', 'Treatment length', 'Number of aligners required', 'What is included in your treatment fee', 'Insurance benefits', 'Available payment options'),
                    'list_style' => 'check',
                    'outro' => $lines(
                        'An individualized treatment estimate is more useful than relying on a generic online price.',
                        "At Ignite Orthodontics, we'll explain your expected treatment costs and available payment options before you begin."
                    )],

                ['_type' => 'pricing', 'tone' => 'navy', 'eyebrow' => 'Paying for Treatment', 'heading' => 'Insurance and *Payment Options*',
                    'items' => [
                        ['icon' => 'shield', 'title' => 'Insurance', 'text' => 'Some dental insurance plans include orthodontic benefits, although coverage varies by plan. Your plan may have age restrictions, deductibles, waiting periods, or a lifetime orthodontic maximum. Our team can help verify your benefits and explain what your insurance may contribute toward treatment.'],
                        ['icon' => 'wallet', 'title' => 'Flexible Payment Options', 'text' => 'Ask about available payment plans that may allow you to spread the cost of treatment over time.'],
                    ],
                    'button' => 'Schedule a Consultation'],

                ['_type' => 'doctor', 'tone' => 'white', 'eyebrow' => 'Meet Dr. Ali Zeitoun, DDS, MBA', 'heading' => 'Your Clear Aligner Treatment Starts With *the Right Team*',
                    'name' => 'Dr. Ali Zeitoun, DDS, MBA', 'credential' => 'Board-Certified Orthodontist',
                    'intro' => $lines(
                        'Dr. Zeitoun provides orthodontic care for children, teens, and adults with an emphasis on individualized treatment planning.',
                        "Clear aligners aren't appropriate for every orthodontic problem. Dr. Zeitoun will evaluate your teeth and bite and determine whether aligners are a suitable option for your treatment."
                    )],

                ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'Why Ignite', 'heading' => 'Why Choose Ignite Orthodontics for *Clear Aligners?*',
                    'intro' => "Here's why you should choose Ignite Orthodontics for clear aligners:",
                    'items' => $cards(
                        ['badge', 'Board-Certified Orthodontic Care', 'Your treatment is planned and monitored by a board-certified orthodontist.'],
                        ['heart', 'Personalized Treatment', 'Your aligner treatment is based on your individual orthodontic needs and treatment goals.'],
                        ['eye', 'Discreet Treatment Option', 'Clear aligners provide a less noticeable alternative to traditional braces for appropriate cases.'],
                        ['chat', 'No-Cost Consultations', 'Find out whether clear aligners are appropriate for your smile before committing to treatment.'],
                        ['wallet', 'Flexible Payment Options', 'Ask about financing and other payment options that may make treatment more manageable.'],
                        ['pin', 'Multiple Michigan Locations', 'Ignite Orthodontics has locations in Sterling Heights, Madison Heights, Farmington Hills, and Flint.']
                    )],

                ['_type' => 'reviews', 'tone' => 'white', 'eyebrow' => 'Patient Stories', 'heading' => 'What Our *Patients Say*',
                    'items' => [
                        ['quote' => "I wanted something that wouldn't be as noticeable at work, and the team helped me understand whether clear aligners were a good fit for my teeth. The process has been easy to follow.", 'name' => 'Danielle R.'],
                        ['quote' => 'I liked being able to remove the aligners when I ate and brush my teeth normally. The team also made sure I understood how important it was to wear them consistently.', 'name' => 'Marcus T.'],
                        ['quote' => 'I had a lot of questions before starting treatment, especially about how long it would take and what I would need to do each day. Everything was explained clearly.', 'name' => 'Jasmine W.'],
                        ['quote' => "The aligners have been much less noticeable than I expected. I appreciate that the team has been available whenever I've had a question.", 'name' => 'Kevin M.'],
                    ]],

                ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Schedule Your Clear Aligner Consultation', 'heading' => 'Find Out if Clear Aligners Are *Right for You*',
                    'intro' => $lines(
                        "Clear aligners can be a convenient and discreet way to straighten teeth, but they're not the right treatment for everyone.",
                        'A consultation gives you the opportunity to have your teeth and bite evaluated, discuss your goals, and find out whether clear aligners are appropriate for your case.'
                    ),
                    'button' => 'Schedule a No-Cost Consultation', 'call' => 'yes'],

                ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Frequently Asked Questions About *Clear Aligners*',
                    'items' => $faq(
                        ['What are clear aligners?', 'Clear aligners are removable orthodontic trays designed to gradually move your teeth into planned positions.'],
                        ['Are clear aligners better than braces?', 'Not necessarily. Clear aligners and traditional braces have different advantages and limitations. The appropriate option depends on your orthodontic needs, the movements required, and your ability to follow treatment instructions.'],
                        ['Can adults get clear aligners?', 'Yes. Clear aligners can be an option for many adults, depending on their orthodontic needs.'],
                        ['Can teenagers use clear aligners?', "Some teenagers may be suitable candidates for clear aligner treatment. Your orthodontist will evaluate your teen's teeth, bite, and ability to follow the treatment plan."],
                        ['Do I have to wear clear aligners all day?', "You'll need to follow your orthodontist's instructions about when and how long to wear your aligners. Consistent wear is important for treatment to progress as planned."],
                        ['Can I eat while wearing clear aligners?', "You'll generally remove clear aligners when eating unless your orthodontist gives you different instructions. Follow the specific guidance provided for your treatment."],
                        ['How long does clear aligner treatment take?', 'Treatment time varies. It depends on the complexity of your case, the amount of movement required, and how consistently you follow your treatment plan.'],
                        ['Are clear aligners painful?', 'You may experience temporary pressure or tenderness, particularly when starting a new set of aligners. Significant or persistent pain should be discussed with your orthodontic team.'],
                        ['What happens if I lose my clear aligner?', "Contact your orthodontic office for instructions. Don't automatically move to the next aligner without guidance, as the appropriate next step depends on your treatment stage."],
                        ['Do clear aligners work for overbites and underbites?', 'Clear aligners can treat some bite problems, but suitability depends on the specific type and severity of the problem. An orthodontic evaluation is needed to determine whether aligners are appropriate.'],
                        ['How much do clear aligners cost?', "The cost varies based on treatment complexity, length, what's included in the treatment fee, insurance coverage, and payment options. An individualized treatment estimate will give you a more accurate picture of your expected cost."],
                        ['Do I need retainers after clear aligner treatment?', 'Yes, retainers are generally needed after active orthodontic treatment to help maintain the position of your teeth. Your orthodontist will provide specific instructions for retainer use.']
                    )],
            ],
        ],

        /* ================================================================
           INVISALIGN
           ================================================================ */
        [
            'slug' => 'invisalign',
            'title' => 'Invisalign',
            'seo_title' => 'Invisalign From $185/Month | Ignite Orthodontics',
            'description' => 'Invisalign clear aligners from $185/month at Ignite Orthodontics. Board-certified orthodontists, insurance accepted and flexible financing.',
            'hero' => [
                'eyebrow' => 'Invisalign',
                'heading' => 'Straighten Your Smile *Without Traditional Braces*',
                'lead' => $lines(
                    "Get the confident smile you're looking for with Invisalign treatment from $185/month.",
                    'Want straighter teeth without brackets and wires? Invisalign uses a series of clear, removable aligners to gradually move your teeth toward their planned positions. At Ignite Orthodontics, your Invisalign treatment is designed and monitored by board-certified orthodontists at a practice dedicated exclusively to orthodontics.'
                ),
                'price' => 'From $185/month*',
                'price_note' => '*Your specific cost depends on your treatment plan and payment arrangements.',
                'button' => $free,
                'points' => $lines('Insurance accepted', 'Flexible financing available', 'No referral required'),
                'image' => '/assets/img/hero2.png',
                'image_alt' => 'Smiling patient holding an Invisalign aligner',
            ],
            'blocks' => [
                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'And We Do It Every Day', 'heading' => 'Orthodontics Is *All We Do.*',
                    'intro' => $lines(
                        'Your smile deserves more than a one-size-fits-all approach.',
                        "At Ignite Orthodontics, orthodontics isn't something we offer alongside general dental services. Orthodontics is our specialty.",
                        'Our board-certified orthodontists focus exclusively on helping patients improve their tooth alignment and bite—from your first consultation through your final retainer.',
                        "Whether you're an adult who wants a more discreet way to straighten your teeth or a teenager looking for an alternative to traditional braces, we'll help you understand whether Invisalign is right for you."
                    ),
                    'button' => $free],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Is Invisalign for You?', 'heading' => 'Want Straighter Teeth *Without Metal Braces?*',
                    'intro' => $lines(
                        "Maybe you've been thinking about orthodontic treatment but don't love the idea of wearing traditional braces.",
                        'You may want to straighten your teeth while keeping your treatment less noticeable.',
                        'You may want the flexibility to remove your aligners when you eat, brush, or have an important event.',
                        "Or maybe you've simply been waiting for a treatment option that fits better into your lifestyle.",
                        "That's where Invisalign may come in. Invisalign uses a series of clear aligners that are custom-made for your treatment plan. Instead of brackets and wires, you'll wear a sequence of aligners that gradually guide your teeth toward their planned positions.",
                        'Invisalign may be a good option if you want:'
                    ),
                    'list' => $lines('A more discreet orthodontic treatment', 'Removable aligners', 'The ability to eat without traditional braces', 'An alternative to brackets and wires', 'A treatment plan designed around your individual smile'),
                    'list_style' => 'check',
                    'outro' => 'The best way to know whether Invisalign is appropriate for you is through an orthodontic evaluation.',
                    'button' => 'Find Out If Invisalign Is Right for You',
                    'image' => '/assets/img/IMG_20260814_125204.jpg', 'image_alt' => 'Patient putting in a clear aligner', 'image_side' => 'left'],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'The Basics', 'heading' => 'What Is *Invisalign?*',
                    'intro' => $lines(
                        'Invisalign is an orthodontic treatment that uses a series of clear, removable aligners to gradually move your teeth.',
                        "Instead of attaching brackets to your teeth, your orthodontist creates a treatment plan that determines the sequence of tooth movements. You'll then progress through a series of aligners according to your treatment plan.",
                        'Each aligner is designed to move your teeth gradually.',
                        'As you progress through treatment, your orthodontist monitors your smile and makes sure your treatment is progressing as planned.',
                        'The result? A more discreet way to receive orthodontic treatment without traditional metal brackets and wires.'
                    ),
                    'button' => $free,
                    'image' => '/assets/img/IMG_20260814_125716.jpg', 'image_alt' => 'A braces model next to a clear aligner', 'image_side' => 'right'],

                ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'How Does Invisalign Work?', 'heading' => 'A Series of Small Changes Can Create *a Big Difference.*',
                    'intro' => $lines(
                        'Your Invisalign treatment begins with an orthodontic evaluation. Your orthodontist examines your teeth and bite and determines whether clear aligners are an appropriate treatment option.',
                        'If Invisalign is right for you, your treatment is planned around the movements your teeth need to make. Your Invisalign journey typically looks like this:'
                    ),
                    'items' => $steps(
                        ['Consultation', "We'll evaluate your teeth, bite, and orthodontic needs and discuss your goals."],
                        ['Personalized Treatment Plan', 'Your orthodontist develops a treatment plan based on your individual smile.'],
                        ['Your Custom Aligners', "You'll receive a series of clear aligners designed for your treatment plan."],
                        ['Wear Your Aligners', "You'll wear your aligners according to your orthodontist's instructions and change to the next aligner in your series as directed."],
                        ['Progress Checks', "You'll have scheduled appointments so your orthodontist can monitor your progress and make adjustments when needed."],
                        ['Your New Smile', "Once active treatment is complete, you'll move into the retention phase to help maintain your results."]
                    ),
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'What Invisalign Treats', 'heading' => 'What Can *Invisalign Correct?*',
                    'intro' => 'Invisalign can be used to address a variety of orthodontic concerns for appropriate candidates.',
                    'items' => $cards(
                        ['crowding', 'Crowded Teeth', "When your teeth don't have enough room to align properly, Invisalign may help gradually move them into better positions."],
                        ['gap', 'Gaps Between Teeth', 'Clear aligners may be used to bring certain spaced teeth closer together.'],
                        ['bite', 'Overbite', 'In appropriate cases, Invisalign can be part of a treatment plan designed to address an overbite.'],
                        ['bite', 'Underbite', 'Some patients with an underbite may be candidates for clear aligner treatment.'],
                        ['cross', 'Crossbite', "Invisalign may be used to address certain crossbite concerns depending on the patient's individual orthodontic needs."],
                        ['tooth', 'Crooked or Misaligned Teeth', 'Clear aligners can gradually move certain rotated, tilted, or misaligned teeth.']
                    ),
                    'note' => $lines('Not every orthodontic case is suitable for Invisalign.', "That's why your treatment should begin with an evaluation from an orthodontist."),
                    'button' => "See If You're a Candidate for Invisalign"],

                ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'Clear Aligners That Fit Into Your Life', 'heading' => 'Invisalign for *Teens & Adults*',
                    'intro' => "Invisalign isn't limited to one age group.",
                    'items' => [
                        ['label' => 'Teens', 'title' => 'Invisalign for Teens', 'text' => $lines(
                            'Teenagers may choose Invisalign because the clear aligners are less noticeable than traditional metal braces.',
                            'For teens who want to straighten their teeth while maintaining their normal appearance, Invisalign may be an option depending on their orthodontic needs and ability to follow the treatment plan.'
                        ), 'link' => '/braces-for-teens/', 'link_label' => 'Braces for teens'],
                        ['label' => 'Adults', 'title' => 'Invisalign for Adults', 'text' => $lines(
                            'For adults, discretion is often an important consideration when choosing orthodontic treatment.',
                            'You may have work meetings, presentations, social events, or simply prefer not to have brackets and wires visible when you smile.',
                            'Clear aligners offer an alternative that can be less noticeable than traditional braces.'
                        ), 'link' => '/braces-for-adults/', 'link_label' => 'Braces for adults'],
                    ],
                    'note' => 'Your orthodontist will determine whether Invisalign is appropriate for your teeth and bite.',
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'The Benefits of Invisalign', 'heading' => 'Orthodontic Treatment Designed Around *Your Lifestyle*',
                    'items' => $cards(
                        ['eye', 'Clear & Discreet', 'The aligners are designed to be much less noticeable than traditional metal braces.'],
                        ['refresh', 'Removable', 'You can remove your aligners when eating, drinking certain beverages, brushing, and flossing.'],
                        ['sparkle', 'No Brackets or Wires', 'There are no traditional metal brackets attached to your teeth.'],
                        ['food', 'Easier Eating', "Because the aligners are removable, you don't have the same food restrictions associated with traditional braces."],
                        ['brush', 'Easy Oral Hygiene', 'Removing your aligners allows you to brush and floss your teeth normally.'],
                        ['shield', 'Fewer Treatment Interruptions', "You don't have brackets and wires that can break in the same way traditional braces can."]
                    ),
                    'note' => 'However, aligners still need to be worn as directed and cared for properly.',
                    'button' => 'Talk to an Orthodontist About Invisalign'],

                ['_type' => 'compare', 'tone' => 'tint', 'eyebrow' => 'Invisalign vs. Traditional Braces', 'heading' => 'Which One Is *Right for You?*',
                    'intro' => 'Both Invisalign and traditional braces can be effective orthodontic treatment options. The right choice depends on your teeth, bite, treatment goals, lifestyle, and orthodontic evaluation.',
                    'columns' => $lines('Invisalign', 'Traditional Braces'),
                    'rows' => $rows(
                        ['Appearance', 'Clear, less noticeable aligners', 'Visible metal brackets and wires'],
                        ['Removable', 'Yes', 'No'],
                        ['Eating', 'Remove aligners before eating', 'Certain foods may need to be avoided'],
                        ['Oral hygiene', 'Remove aligners to brush and floss', 'Requires cleaning around brackets and wires'],
                        ['Customization', 'Clear aligners', 'Elastic bands can be available in different colors'],
                        ['Treatment suitability', 'Depends on individual case', 'Can treat a broad range of orthodontic concerns'],
                        ['Monitoring', 'Regular orthodontic appointments', 'Regular orthodontic appointments']
                    ),
                    'note' => $lines("You don't have to decide before your consultation.", 'Our orthodontists can evaluate your smile and explain which treatment options make sense for you.'),
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'What Is Invisalign Treatment Like?', 'heading' => "Here's What You Need to Know *Before You Start.*",
                    'items' => $cards(
                        ['clock', "You'll Need to Wear Your Aligners Consistently", "Invisalign is removable, but that doesn't mean you should only wear it when it's convenient. Following your orthodontist's instructions is an important part of treatment."],
                        ['food', 'You Remove Them to Eat', "Unlike traditional braces, your aligners can be removed when you eat. That means you don't have to change your diet in the same way you would with fixed braces."],
                        ['brush', "You'll Need to Keep Them Clean", 'Your aligners should be cared for according to the instructions provided by your orthodontic team.'],
                        ['heart', 'You May Feel Pressure', 'As your teeth move, you may experience pressure or temporary discomfort when starting a new set of aligners.'],
                        ['calendar', "You'll Have Progress Appointments", 'Your orthodontist will monitor your treatment throughout the process and make sure your teeth are moving as planned.']
                    )],

                ['_type' => 'pricing', 'tone' => 'navy', 'eyebrow' => 'How Much Does Invisalign Cost?', 'heading' => 'Invisalign From *$185/Month*',
                    'intro' => $lines(
                        "The cost of Invisalign varies because every patient's treatment is different.",
                        'At Ignite Orthodontics, Invisalign treatment starts at $185/month. We offer flexible ways to pay, including:'
                    ),
                    'items' => $pay(
                        'We accept insurance and can help you understand how your orthodontic benefits may apply to treatment.',
                        'Financing options can help spread the cost of treatment into manageable monthly payments.',
                        'You can also choose to pay out of pocket based on the payment arrangements available for your treatment.'
                    ),
                    'note' => $lines('Your specific cost depends on your treatment plan and payment arrangements.', 'Want to understand your options?'),
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'Why Choose Ignite Orthodontics for Invisalign?', 'heading' => 'Your Clear Aligners Are Only *Part of the Treatment.*',
                    'intro' => $lines('The aligners matter.', 'But so does the orthodontist planning and monitoring your treatment.', 'At Ignite Orthodontics, orthodontics is all we do.'),
                    'items' => $cards(
                        ['tooth', 'Orthodontics Only', 'Our practice is dedicated exclusively to orthodontic treatment.'],
                        ['badge', 'Board-Certified Orthodontists', 'Your Invisalign treatment is overseen by board-certified orthodontists.'],
                        ['heart', 'Personalized Treatment', 'Your treatment plan is based on your individual teeth, bite, and orthodontic goals.'],
                        ['wallet', 'Flexible Payment Options', 'Insurance, financing, and out-of-pocket payment options are available.'],
                        ['arrow', 'No Referral Required', 'You can schedule directly with Ignite Orthodontics.'],
                        ['calendar', 'Convenient Appointments', 'Evening and weekend appointments are available to help make treatment easier to fit into your schedule.'],
                        ['pin', 'Multiple Locations', "Choose the Ignite Orthodontics location that's most convenient for you."],
                        ['screen', 'Modern Technology', 'We use modern orthodontic technology to evaluate, plan, and monitor treatment.']
                    ),
                    'button' => $free],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Your Invisalign Treatment Starts With a Consultation', 'heading' => "You Don't Have to Guess Whether *You're a Candidate.*",
                    'intro' => $lines(
                        'You may have spent hours researching Invisalign.',
                        'You may have looked at before-and-after photos.',
                        'You may have even wondered whether your teeth are "too crooked" for clear aligners.',
                        'Instead of guessing, talk to an orthodontist.',
                        "During your free consultation, we'll evaluate your teeth and bite and discuss your treatment options.",
                        "If Invisalign is appropriate for you, we'll explain what your treatment could look like.",
                        "If another treatment option would better address your orthodontic needs, we'll explain that too."
                    ),
                    'button' => $free],

                ['_type' => 'faq', 'tone' => 'tint', 'eyebrow' => 'FAQ', 'heading' => 'Frequently Asked Questions About *Invisalign*',
                    'items' => $faq(
                        ['Is Invisalign as effective as braces?', $lines('Invisalign can be an effective treatment option for many patients, but treatment suitability depends on the individual case.', 'Your orthodontist can determine whether clear aligners are appropriate for your teeth and bite.')],
                        ['How long does Invisalign treatment take?', $lines('Treatment time varies from patient to patient. It depends on factors such as your orthodontic needs, treatment goals, and how your teeth respond to treatment.', 'Your orthodontist can provide a more personalized estimate during your consultation.')],
                        ['Does Invisalign hurt?', 'You may experience pressure or temporary discomfort when you begin wearing a new set of aligners. This is related to the gradual movement of your teeth.'],
                        ['Can I eat with Invisalign?', $lines('You generally remove your aligners when eating. This allows you to enjoy your food without the same dietary restrictions associated with traditional braces.', "You'll receive specific instructions from your orthodontic team.")],
                        ['Can I drink while wearing Invisalign?', "Your orthodontist will provide instructions about what you can drink while wearing your aligners. In general, you'll want to follow the care instructions provided for your specific treatment."],
                        ['Do I have to wear Invisalign all day?', $lines("Invisalign is designed to be worn consistently according to your orthodontist's instructions.", "Because the aligners are removable, it's important to follow your prescribed wear schedule.")],
                        ['Can teenagers get Invisalign?', "Yes. Invisalign may be an option for teenagers who meet the necessary treatment requirements and can follow their orthodontist's instructions."],
                        ['Can adults get Invisalign?', 'Yes. Adults can be candidates for Invisalign depending on their orthodontic needs.'],
                        ['Can Invisalign fix an overbite?', 'Invisalign may be used to address certain overbite cases. Your orthodontist will need to evaluate your bite to determine whether clear aligners are appropriate.'],
                        ['Can Invisalign fix crowded teeth?', $lines('Invisalign may be used to treat certain cases of dental crowding.', 'The severity and nature of the crowding will determine whether clear aligners are appropriate.')],
                        ['Is Invisalign better than braces?', $lines('Invisalign and traditional braces have different characteristics. Neither option is appropriate for every patient.', 'The right treatment depends on your individual orthodontic needs, goals, and preferences.')],
                        ['How much does Invisalign cost?', $lines('Ignite Orthodontics offers Invisalign from $185/month. Your actual cost depends on your treatment plan and payment arrangements.', 'Insurance, financing, and out-of-pocket payment options are available.')],
                        ['Do I need a referral to get Invisalign?', 'No. You can schedule directly with Ignite Orthodontics without a referral from your general dentist.'],
                        ['Can I start Invisalign on the same day as my consultation?', $lines('Depending on your individual circumstances and appointment availability, same-day treatment may be possible for eligible patients.', 'Ask our team when scheduling your consultation.')],
                        ['What happens after Invisalign treatment?', "After active treatment, you'll typically move into the retention phase. Retainers help maintain the position of your teeth after orthodontic treatment."]
                    )],

                ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Your First Step Is a Free Consultation', 'heading' => 'Ready to See If Invisalign Is *Right for You?*',
                    'intro' => $lines("You don't need to decide between Invisalign and braces before you visit us.", 'Let an orthodontist evaluate your smile, answer your questions, and explain your options.'),
                    'price' => 'Invisalign From $185/Month',
                    'button' => $free, 'call' => 'yes'],
            ],
        ],

        /* ================================================================
           BRACES FOR ADULTS (the practice's braces page, published as written)
           ================================================================ */
        [
            'slug' => 'braces-for-adults',
            'title' => 'Braces for Adults',
            'seo_title' => 'Braces for Adults | Ignite Orthodontics',
            'description' => 'Braces at Ignite Orthodontics: metal, ceramic, self-ligating and lingual options for straighter teeth, a stronger bite and lasting results.',
            'hero' => [
                'eyebrow' => 'Braces Treatment at Ignite Orthodontics',
                'heading' => 'Straighter Teeth. Stronger Bite. *Lasting Results.*',
                'lead' => 'Braces are one of the most reliable ways to correct misaligned teeth, improve bite function, and create a healthier, more confident smile. At Ignite Orthodontics, we make the process clear, comfortable, and fully tailored to your needs from start to finish.',
                'button' => 'Book a Free Consultation',
                'points' => '',
                'image' => '/assets/img/invisalign-braces-scaled.jpg',
                'image_alt' => 'Smiling patient with braces holding teeth models',
            ],
            'blocks' => [
                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Why Braces', 'heading' => 'Why Braces Still Remain *the Gold Standard*',
                    'intro' => $lines(
                        'Braces offer precise, controlled movement of the teeth, making them highly effective for both simple and complex orthodontic concerns. They work continuously to guide your teeth into proper alignment, delivering predictable, long-lasting results.',
                        'Whether you are dealing with crowding, spacing, bite issues, or overall misalignment, braces provide a structured and proven path to a healthier smile.',
                        'At Ignite Orthodontics, we focus on making the experience as straightforward and comfortable as possible, with clear guidance at every stage.'
                    ),
                    'image' => '/assets/img/IMG_20260814_125146.jpg', 'image_alt' => 'Close-up of a smile with braces', 'image_side' => 'right'],

                ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'Braces Options', 'heading' => 'Find the Right Braces *for Your Smile*',
                    'intro' => 'We offer different types of braces to suit your needs, lifestyle, and treatment goals.',
                    'items' => [
                        ['label' => 'Most Widely Used', 'title' => 'Metal Braces', 'text' => $lines(
                            'Metal braces are the most traditional and widely used orthodontic treatment. They are strong, reliable, and highly effective for treating a wide range of alignment and bite issues.',
                            'They remain a great option for children, teens, and adults who want consistent and predictable results.'
                        ), 'link' => '/types-of-braces/traditional-braces/', 'link_label' => 'Learn about traditional braces'],
                        ['label' => 'More Discreet', 'title' => 'Ceramic Braces', 'text' => $lines(
                            'Ceramic braces work in the same way as metal braces but use tooth-coloured brackets that blend more naturally with your smile.',
                            'They are a popular choice for patients who want effective treatment with a more discreet appearance.'
                        ), 'link' => '/types-of-braces/ceramic-braces/', 'link_label' => 'Learn about ceramic braces'],
                        ['label' => 'Modern Clip System', 'title' => 'Self-Ligating Braces', 'text' => $lines(
                            'Self-ligating braces use a modern clip system instead of elastic bands to hold the wire in place. This allows for smoother tooth movement and may reduce the number of adjustment visits required.',
                            'They are designed for efficiency, comfort, and improved hygiene during treatment.'
                        ), 'link' => '', 'link_label' => ''],
                        ['label' => 'Behind the Teeth', 'title' => 'Lingual Braces', 'text' => $lines(
                            'Lingual braces are attached to the back surfaces of your teeth, so they are hidden from view when you smile. They work in the same way as other fixed braces, using brackets and wires to guide your teeth into place.',
                            'They can be a good choice for patients who want fixed treatment that is not visible from the front. Your orthodontist will let you know whether lingual braces are suitable for your bite.'
                        ), 'link' => '', 'link_label' => ''],
                    ]],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Who Braces Are For', 'heading' => 'Who Can Benefit *From Braces?*',
                    'intro' => 'Braces can effectively treat a wide range of orthodontic concerns, including:',
                    'list' => $lines('Crooked or rotated teeth', 'Crowding caused by lack of space', 'Gaps between teeth', 'Overbite', 'Underbite', 'Crossbite', 'Open bite', 'General alignment and spacing issues'),
                    'list_style' => 'check',
                    'outro' => 'Braces are suitable for both children and adults. Whether treatment is early or later in life, the goal remains the same: a healthier bite and a more confident smile.'],

                ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'How Braces Work', 'heading' => 'How the Braces *Process Works*',
                    'intro' => 'Your treatment follows a clear, structured process designed to guide you from consultation to results with confidence.',
                    'items' => $steps(
                        ['Free Consultation', 'We assess your teeth, bite, and smile goals to understand what you need.'],
                        ['Digital Assessment', 'Advanced imaging helps us create a detailed view of your teeth and jaw alignment.'],
                        ['Personalised Treatment Plan', 'You receive a clear explanation of your treatment options, timeline, and cost.'],
                        ['Braces Placement', 'Your braces are carefully fitted, and we guide you through what to expect.'],
                        ['Regular Adjustments', 'We monitor your progress and make small adjustments to guide tooth movement.'],
                        ['Completion and Retainers', 'Once treatment is complete, retainers help protect and maintain your new smile.']
                    )],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Benefits', 'heading' => 'What Braces Can *Help Improve*',
                    'intro' => $lines('Braces do more than straighten teeth. They help improve both function and long-term oral health.', 'With treatment, you can:'),
                    'list' => $lines('Improve bite alignment and chewing function', 'Make teeth easier to clean and maintain', 'Reduce uneven wear on teeth', 'Support healthier gums and long-term dental health', 'Enhance overall smile appearance and facial balance'),
                    'list_style' => 'check',
                    'outro' => 'A properly aligned smile is not just cosmetic. It plays an important role in your overall oral health.',
                    'image' => '/assets/img/IMG_20260814_125736.jpg', 'image_alt' => 'Smiling family outdoors', 'image_side' => 'left'],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Braces vs Other Options', 'heading' => 'Are Braces *Right for You?*',
                    'intro' => 'Braces are often recommended when more precise or complex tooth movement is needed. They are especially effective for:',
                    'list' => $lines('Severe crowding or spacing issues', 'Complex bite corrections', 'Cases requiring full control of tooth movement', 'Patients who prefer a fixed treatment option'),
                    'list_style' => 'check',
                    'outro' => 'Invisalign may also be an option for some patients. During your consultation, we will help you understand which treatment best fits your needs and lifestyle.'],

                ['_type' => 'panels', 'tone' => 'white',
                    'items' => [
                        ['label' => 'What to Expect', 'title' => 'What Your Braces Journey Feels Like', 'text' => $lines(
                            'It is normal to feel mild pressure or discomfort when braces are first placed or adjusted. This typically settles as your mouth adapts.',
                            'Most patients adjust within a few days and continue treatment comfortably with regular check-ins.',
                            'Treatment time varies, but most cases are completed within 12 to 24 months depending on complexity and consistency.'
                        ), 'link' => '', 'link_label' => ''],
                        ['label' => 'Aftercare', 'title' => 'Protecting Your New Smile', 'text' => $lines(
                            'Once your braces are removed, retainers are essential to maintain your results. Teeth naturally shift over time, and retainers help keep everything in place.',
                            'We provide clear instructions and ongoing support to ensure your results last for years to come.'
                        ), 'link' => '', 'link_label' => ''],
                    ]],

                ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'Patient Experiences', 'heading' => 'Real Patients. *Real Braces Results.*',
                    'items' => [
                        ['quote' => 'I avoided braces for years, but the team made everything simple. I always knew what was happening, and my bite feels completely different now.', 'name' => 'Sarah M.'],
                        ['quote' => 'My son just finished his braces treatment and the improvement is incredible. Every appointment was smooth and well organised.', 'name' => 'Jason T.'],
                        ['quote' => 'I was nervous about getting braces as an adult, but it turned out to be much easier than I expected. The results are worth it.', 'name' => 'Michael D.'],
                        ['quote' => 'My daughter felt comfortable from the very first visit. The staff were patient and kind throughout her entire treatment.', 'name' => 'Heather S.'],
                        ['quote' => 'The difference in my smile and bite is life-changing. I only wish I had started sooner.', 'name' => 'Brandon W.'],
                    ]],

                ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Braces Questions, *Answered*',
                    'items' => $faq(
                        ['How long will I need braces?', 'Most treatments last between 12 and 24 months depending on your case.'],
                        ['Do braces hurt?', 'You may feel mild pressure after placement or adjustments, but it is temporary and manageable.'],
                        ['Can adults get braces?', 'Yes. Braces are effective at any age, and many of our patients are adults.'],
                        ['How often are appointments needed?', 'Typically every 4 to 8 weeks for adjustments and progress checks.'],
                        ['Are there foods I should avoid?', 'Yes. Hard, sticky, or chewy foods can damage braces and should be avoided during treatment.'],
                        ['Will I need a retainer afterward?', 'Yes. Retainers are essential to maintain your results long-term.']
                    )],

                ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Your Braces Consultation', 'heading' => 'Ready to Start Your *Braces Journey?*',
                    'intro' => 'Take the first step toward a healthier, straighter smile with a personalised braces consultation at Ignite Orthodontics. We will guide you through your options and create a plan that fits your goals.',
                    'button' => 'Book a Free Consultation', 'call' => 'yes'],
            ],
        ],

        /* ================================================================
           BRACES FOR TEENS
           ================================================================ */
        [
            'slug' => 'braces-for-teens',
            'title' => 'Braces for Teens',
            'seo_title' => 'Braces for Teens From $99/Month | Ignite Orthodontics',
            'description' => 'Braces for teens from $99/month at Ignite Orthodontics. Board-certified orthodontists, teen-friendly care, insurance and flexible financing.',
            'hero' => [
                'eyebrow' => 'Braces for Teens',
                'heading' => 'Give Your Teen a Smile *They Can Feel Confident About.*',
                'lead' => $lines(
                    'Braces can help straighten crooked teeth, improve bite problems, and create a healthier-looking smile during the teen years.',
                    "At Ignite Orthodontics, orthodontics is all we do. Your teen's treatment is planned and monitored by board-certified orthodontists who understand that orthodontic treatment isn't just about moving teeth; it's about making the experience work for your teen's everyday life."
                ),
                'price' => 'Braces from $99/month*',
                'price_note' => '*Monthly payment depends on treatment, insurance coverage, and payment arrangement.',
                'button' => $free,
                'points' => $lines('Free consultations', 'Flexible financing', 'Insurance accepted'),
                'image' => '/assets/img/home-page-hero-image-kids-ortho.jpg',
                'image_alt' => 'Smiling teenager with braces',
            ],
            'blocks' => [
                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Is It Time?', 'heading' => "Your Teen's Smile Is Changing. *This Is a Good Time to Check In.*",
                    'intro' => $lines(
                        'The teenage years are when a lot changes.',
                        'School. Friends. Sports. Photos. Prom. Graduation. Confidence.',
                        "It's also an important time to pay attention to your teen's teeth and bite.",
                        "Maybe you've noticed:"
                    ),
                    'list' => $lines('Teeth that are crowded or overlapping', 'Large spaces between teeth', 'Teeth that stick out', 'An overbite or underbite', 'A crossbite', 'Crooked teeth', 'Difficulty biting or chewing', 'Your teen becoming self-conscious about their smile'),
                    'list_style' => 'check',
                    'outro' => $lines(
                        'You may be wondering whether now is the right time for braces. A consultation can give you the answers.',
                        "At Ignite Orthodontics, we'll evaluate your teen's teeth and bite, explain what we see, and walk you through the treatment options that may be appropriate."
                    ),
                    'button' => "Schedule Your Teen's Free Consultation",
                    'image' => '/assets/img/istockphoto-2166449878-612x612-1.webp', 'image_alt' => 'Two smiling teenagers outdoors', 'image_side' => 'right'],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'More Than a Straighter Smile', 'heading' => "Orthodontics for Teens Isn't Just About *a Straighter Smile*",
                    'intro' => $lines('For parents, orthodontic treatment can feel like a medical decision.', 'For teens, it can feel much more personal.', 'They may be thinking:'),
                    'list' => $lines('"Will everyone notice my braces?"', '"Will I look different in pictures?"', '"Can I still play sports?"', '"What if my friends make fun of me?"', '"How long will I have to wear them?"'),
                    'list_style' => 'quote',
                    'outro' => $lines(
                        'Those concerns matter.',
                        "That's why our approach is about more than simply putting braces on your teen's teeth. We want them to understand their treatment, feel comfortable asking questions, and know what to expect along the way."
                    )],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Signs to Look For', 'heading' => 'What Are the Signs *Your Teen May Need Braces?*',
                    'intro' => $lines('Not every orthodontic problem is obvious.', 'Some signs that your teen may benefit from an orthodontic evaluation include:'),
                    'items' => $cards(
                        ['crowding', 'Crowded Teeth', 'Teeth may overlap, twist, or compete for space.'],
                        ['gap', 'Gaps Between Teeth', 'Spaces can develop between teeth for several reasons and may be addressed through orthodontic treatment.'],
                        ['bite', 'Overbite', 'The upper teeth extend too far over the lower teeth.'],
                        ['bite', 'Underbite', 'The lower teeth extend in front of the upper teeth.'],
                        ['cross', 'Crossbite', 'Some upper teeth sit inside the lower teeth when the mouth is closed.'],
                        ['tooth', 'Crooked or Misaligned Teeth', "Teeth may not line up properly even when there isn't obvious crowding."],
                        ['food', 'Bite or Chewing Problems', "The way your teen's upper and lower teeth come together can affect how they bite and chew."]
                    ),
                    'note' => "The only way to know whether your teen needs orthodontic treatment is to have their teeth and bite evaluated by an orthodontist.",
                    'button' => 'Schedule a Free Consultation'],

                ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'Your First Visit', 'heading' => 'What Happens at a Teen *Orthodontic Consultation?*',
                    'intro' => 'We keep the first step simple.',
                    'items' => $steps(
                        ['We Get to Know Your Teen', "We'll talk about their smile, concerns, goals, and what brought you to Ignite."],
                        ['We Evaluate Their Teeth and Bite', 'An orthodontic evaluation helps us understand how the teeth and jaws are developing and whether treatment may be appropriate.'],
                        ['We Explain the Options', "If orthodontic treatment is recommended, we'll explain the options available for your teen and why a particular approach may be appropriate."],
                        ['We Discuss Timing', "Not every teen needs treatment immediately. We'll explain whether now is the right time to begin based on their individual situation."],
                        ['We Explain the Financial Options', "We'll walk you through insurance, financing, and out-of-pocket payment options so you understand what treatment may cost."]
                    ),
                    'note' => "No pressure. No confusing orthodontic terminology. Just a clear conversation about your teen's smile.",
                    'button' => $free],

                ['_type' => 'panels', 'tone' => 'white', 'eyebrow' => 'Treatment Options', 'heading' => 'What Braces Options Are *Available for Teens?*',
                    'intro' => $lines("Today's teens have more than one way to straighten their teeth.", "Depending on your teen's orthodontic needs, options may include:"),
                    'items' => [
                        ['label' => 'Fixed', 'title' => 'Traditional Metal Braces', 'text' => 'A tried-and-true fixed orthodontic option using brackets and wires to gradually move the teeth.', 'link' => '/types-of-braces/traditional-braces/', 'link_label' => 'Learn About Traditional Braces'],
                        ['label' => 'Discreet', 'title' => 'Ceramic Braces', 'text' => 'Tooth-colored or clear brackets provide a more discreet appearance while remaining fixed to the teeth.', 'link' => '/types-of-braces/ceramic-braces/', 'link_label' => 'Learn About Ceramic Braces'],
                        ['label' => 'Removable', 'title' => 'Invisalign', 'text' => 'Clear, removable aligners that offer a discreet alternative to traditional braces for patients who are appropriate candidates.', 'link' => '/invisalign/', 'link_label' => 'Learn About Invisalign'],
                    ],
                    'note' => $lines("Your teen doesn't need to decide which treatment they want before their consultation.", "We'll help you understand which options may be appropriate for their smile.")],

                ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'Life With Braces', 'heading' => "Braces Should Fit Into *Your Teen's Life*",
                    'intro' => $lines("Orthodontic treatment is a commitment, but your teen's life doesn't stop because they have braces.", 'They can still:'),
                    'items' => $cards(
                        ['ball', 'Play Sports', 'Teens who participate in sports can discuss appropriate orthodontic protection and care with their orthodontist.'],
                        ['camera', 'Take Photos', 'Braces are part of the journey. Many teens also choose braces colors as a way to personalize their look.'],
                        ['book', 'Go to School', "Braces don't prevent your teen from attending school, participating in activities, or spending time with friends."],
                        ['food', 'Enjoy Their Favorite Foods With Some Changes', 'Your orthodontic team will explain which foods to avoid and how to care for braces properly.'],
                        ['brush', 'Keep Up With Their Routine', 'Regular brushing, flossing, and orthodontic appointments are important throughout treatment.']
                    )],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Personal Style', 'heading' => 'Let Them Make Their Braces *Their Own*',
                    'intro' => $lines(
                        'For some teens, braces are something they want to minimize.',
                        "For others, they're an opportunity to express themselves.",
                        'With traditional braces, teens can often choose colors for their elastic ties during adjustment visits.',
                        'Whether they want subtle colors, school colors, or something completely different, their smile journey can feel like theirs.'
                    ),
                    'image' => '/assets/img/IMG_20260814_125055.jpg', 'image_alt' => 'Teenager smiling with colorful braces', 'image_side' => 'left'],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Treatment Time', 'heading' => 'How Long Will My Teen *Need Braces?*',
                    'intro' => $lines("There's no single treatment timeline for every teenager.", 'Treatment length depends on factors such as:'),
                    'list' => $lines('The orthodontic problems being treated', 'The complexity of the case', "How your teen's teeth respond to treatment", 'Following orthodontic instructions', 'Keeping scheduled appointments', 'Taking proper care of braces'),
                    'list_style' => 'check',
                    'outro' => 'During your consultation, the orthodontist can discuss the expected treatment timeline for your teen.',
                    'button' => "Find Out What Your Teen's Treatment Could Look Like"],

                ['_type' => 'pricing', 'tone' => 'navy', 'eyebrow' => 'How Much Do Braces for Teens Cost?', 'heading' => 'Braces From *$99/Month*',
                    'intro' => $lines('We know orthodontic treatment is a significant investment for many families.', "That's why Ignite Orthodontics offers flexible ways to pay."),
                    'items' => $pay(
                        'We accept insurance and can help you understand how your orthodontic benefits may apply.',
                        'Flexible financing options may be available to spread payments over time.',
                        "We'll explain your payment options so you can choose the arrangement that works for your family."
                    ),
                    'note' => $lines(
                        "Subject to approval. Your actual monthly payment and total treatment cost depend on your teen's treatment plan, insurance coverage, and payment arrangement.",
                        'Start with a free consultation to understand your options.'
                    ),
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Why Families Choose Ignite Orthodontics', 'heading' => 'Orthodontics. *Nothing Else.*',
                    'intro' => $lines("When your teen starts orthodontic treatment, you want a team that understands orthodontics, not a practice where orthodontics is just one of many services.", "That's Ignite."),
                    'items' => $cards(
                        ['badge', 'Board-Certified Orthodontists', "Your teen's treatment is planned and monitored by board-certified orthodontists."],
                        ['heart', 'Teen-Friendly Care', 'We understand that teenagers have different concerns, questions, and priorities. We want them to feel comfortable throughout treatment.'],
                        ['layers', 'Multiple Treatment Options', "Depending on your teen's needs, treatment options may include traditional braces, ceramic braces, and Invisalign."],
                        ['wallet', 'Flexible Payment Options', 'Insurance, financing, and out-of-pocket payment options are available.'],
                        ['calendar', 'Convenient Appointments', 'Evening and weekend appointments may be available to make orthodontic visits easier to fit around school and family schedules.'],
                        ['pin', 'Multiple Locations', 'Choose an Ignite Orthodontics location that works for your family.'],
                        ['arrow', 'No Referral Required', 'You can schedule a consultation directly with our team.']
                    )],

                ['_type' => 'faq', 'tone' => 'tint', 'eyebrow' => 'FAQ', 'heading' => 'What Parents *Want to Know*',
                    'items' => $faq(
                        ['Will braces hurt my teen?', 'Your teen may experience some pressure, tenderness, or discomfort when braces are placed or adjusted. This is typically temporary. Your orthodontic team will explain what to expect and how to manage it.'],
                        ['Can my teen play sports with braces?', 'Yes. Teens can generally continue participating in sports while undergoing orthodontic treatment. Your orthodontist can recommend appropriate protection and provide activity-specific guidance.'],
                        ['Can my teen get Invisalign instead of braces?', "Invisalign may be an option for some teens. Whether it's appropriate depends on your teen's teeth, bite, treatment needs, and ability to follow the aligner wear instructions."],
                        ["What if my teen doesn't want braces?", $lines("That's a common concern.", 'The best first step is a conversation, not pressure.', 'A consultation gives your teen the opportunity to ask questions, understand their options, and learn what treatment would actually involve.')],
                        ['How often will my teen need orthodontic appointments?', "Your orthodontist will establish an appointment schedule based on your teen's treatment plan and progress."],
                        ['Does my teen need to see a dentist before seeing an orthodontist?', 'Your teen should continue regular dental checkups during orthodontic treatment. If there are existing dental concerns that need attention, your orthodontic team can discuss the appropriate next step.'],
                        ['Does my teen need a referral?', 'No. A referral is not required to schedule a consultation with Ignite Orthodontics.'],
                        ['Can braces be placed on the same day as the consultation?', 'Same-day treatment may be possible for eligible patients depending on individual circumstances and appointment availability. Ask our team when scheduling.']
                    )],

                ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => "Schedule Your Teen's Free Consultation", 'heading' => "Your Teen's Smile Starts With *One Conversation*",
                    'intro' => $lines(
                        "You don't need to know which braces your teen needs.",
                        "You don't need to know how long treatment will take.",
                        "And you don't need to have everything figured out before you call.",
                        "That's what the consultation is for. We'll evaluate your teen's smile, answer your questions, explain the available options, and help you understand the next step."
                    ),
                    'button' => $free, 'call' => 'yes'],
            ],
        ],

        /* ================================================================
           TRADITIONAL BRACES (new, under Types of Braces)
           ================================================================ */
        [
            'parent' => 'types-of-braces',
            'slug' => 'traditional-braces',
            'title' => 'Traditional Braces',
            'menu_order' => 20,
            'seo_title' => 'Traditional Braces From $99/Month | Ignite Orthodontics',
            'description' => 'Traditional metal braces from $99/month at Ignite Orthodontics. Expert orthodontic care for kids, teens and adults, with insurance and financing.',
            'hero' => [
                'eyebrow' => 'Traditional Braces',
                'heading' => 'Get a Straighter Smile With Traditional Braces *From $99/Month*',
                'lead' => $lines(
                    'Expert orthodontic care for kids, teens, and adults—with flexible ways to pay.',
                    'If crowded teeth, gaps, or a bite problem are affecting your smile, traditional braces may be a great place to start.',
                    'At Ignite Orthodontics, orthodontics is all we do. Our board-certified orthodontists create personalized treatment plans designed around your teeth, your bite, and your goals.'
                ),
                'price' => 'From $99/month',
                'price_note' => 'Your total treatment cost depends on your individual treatment plan and payment arrangements.',
                'button' => $free,
                'points' => $lines('No referral required', 'Insurance accepted', 'Flexible financing available'),
                'image' => '/assets/img/IMG_20260814_125131.jpg',
                'image_alt' => 'Close-up of traditional metal braces',
            ],
            'blocks' => [
                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'And We Do It Every Day', 'heading' => 'Orthodontics Is *All We Do.*',
                    'intro' => $lines(
                        'Choosing an orthodontist is different from choosing a general dentist.',
                        "At Ignite Orthodontics, orthodontics isn't one service on a long list of dental procedures. It is our entire practice.",
                        'That means every part of your experience—from your consultation and treatment plan to your braces adjustments and retention—is centered around orthodontic care.',
                        'Your orthodontic care includes:'
                    ),
                    'items' => $cards(
                        ['badge', 'Board-Certified Orthodontists', 'Your treatment is overseen by board-certified orthodontists focused exclusively on orthodontic care.'],
                        ['wallet', 'Flexible Payment Options', 'We accept insurance, offer financing options, and work with patients who prefer to pay out of pocket.'],
                        ['arrow', 'No Referral Required', "You can schedule directly with Ignite Orthodontics. You don't need to wait for a referral from your dentist."],
                        ['calendar', 'Convenient Scheduling', 'Evening and weekend appointments make it easier to fit orthodontic visits into your schedule.'],
                        ['screen', 'Modern Orthodontic Technology', 'We use modern technology to help evaluate your smile and plan your treatment.'],
                        ['users', 'Care for the Whole Family', 'Traditional braces are available for children, teens, and adults.']
                    ),
                    'button' => $free],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Is It Time?', 'heading' => 'Is It Time to Do Something *About Your Smile?*',
                    'intro' => $lines(
                        "You may have gotten used to your teeth looking a certain way.",
                        "Maybe they're crowded. Maybe there's a noticeable gap. Maybe your bite doesn't feel quite right.",
                        "Or maybe you've simply been thinking about getting braces for years but haven't taken the first step.",
                        "You don't have to know exactly what treatment you need before contacting us. That's what the consultation is for.",
                        "At your visit, we'll evaluate your teeth and bite, discuss your concerns, and explain your treatment options.",
                        'You may be considering braces if you have:'
                    ),
                    'list' => $lines('Crowded or overlapping teeth', 'Gaps or spaces between teeth', 'An overbite', 'An underbite', 'A crossbite', 'Rotated or misaligned teeth', "Teeth that don't come together properly", 'Concerns about the appearance of your smile'),
                    'list_style' => 'check',
                    'button' => 'Find Out If Braces Are Right for You'],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'How They Work', 'heading' => 'Traditional Braces: *A Proven Way to Straighten Teeth*',
                    'intro' => $lines(
                        'Traditional braces use brackets and orthodontic wires to gradually guide your teeth into their planned positions.',
                        'The brackets are attached to your teeth and connected by an archwire. Over the course of treatment, your orthodontist makes adjustments that help guide tooth movement.',
                        "Because traditional braces remain attached throughout treatment, you don't have to remember to put them in or take them out.",
                        "They're also capable of treating a wide range of alignment and bite concerns."
                    ),
                    'image' => '/assets/img/IMG_20260814_125055.jpg', 'image_alt' => 'Smiling patient with traditional braces', 'image_side' => 'right'],

                ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'What They Offer', 'heading' => 'Traditional Braces *Can Offer*',
                    'items' => $cards(
                        ['sparkle', 'Controlled Tooth Movement', 'Brackets and wires give your orthodontist control over how your teeth move during treatment.'],
                        ['layers', 'A Treatment Option for Many Cases', 'Traditional braces can be used for many different alignment and bite concerns.'],
                        ['clock', 'Continuous Treatment', 'Your braces remain in place throughout active treatment.'],
                        ['heart', 'A Personalized Treatment Plan', 'Your orthodontist determines how your braces should be used based on your individual smile and orthodontic needs.']
                    )],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Alignment & Bite', 'heading' => 'What Can Traditional *Braces Correct?*',
                    'intro' => $lines("Traditional braces aren't just about straight teeth.", 'Orthodontic treatment can address both tooth alignment and bite concerns.'),
                    'items' => $cards(
                        ['crowding', 'Crowded Teeth', "When your teeth don't have enough room, they can overlap or become rotated. Braces can gradually create better alignment."],
                        ['gap', 'Spacing & Gaps', 'Braces can help bring teeth closer together when unwanted spaces exist between them.'],
                        ['bite', 'Overbite', 'An overbite occurs when the upper teeth extend too far over the lower teeth.'],
                        ['bite', 'Underbite', 'An underbite occurs when the lower teeth extend in front of the upper teeth.'],
                        ['cross', 'Crossbite', 'A crossbite occurs when some upper teeth sit inside the lower teeth when you bite down.'],
                        ['tooth', 'Other Alignment Concerns', 'Teeth that are rotated, tilted, or positioned incorrectly may also benefit from orthodontic treatment.']
                    ),
                    'note' => 'The right treatment depends on your individual orthodontic evaluation.',
                    'button' => $free],

                ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'One Treatment. Different Reasons to Start.', 'heading' => 'Braces for Kids, *Teens & Adults*',
                    'intro' => 'Traditional braces can be used at different stages of life.',
                    'items' => [
                        ['label' => 'Kids', 'title' => 'Braces for Kids', 'text' => $lines(
                            'An orthodontic evaluation can identify developing alignment and bite concerns and help determine whether treatment is appropriate and when it should begin.',
                            "If braces are recommended, we'll create a treatment plan based on your child's individual development and orthodontic needs."
                        ), 'link' => '/braces-for-kids/', 'link_label' => 'Braces for kids'],
                        ['label' => 'Teens', 'title' => 'Braces for Teens', 'text' => $lines(
                            'For many teenagers, braces are an important step toward a healthier, more confident smile.',
                            'Traditional metal braces are durable and can address a broad range of orthodontic concerns.',
                            'And teens can add a little personality to treatment by choosing different elastic band colors.'
                        ), 'link' => '/braces-for-teens/', 'link_label' => 'Braces for teens'],
                        ['label' => 'Adults', 'title' => 'Braces for Adults', 'text' => $lines(
                            "You don't have to be a teenager to get braces.",
                            "Adults choose orthodontic treatment for many reasons. You may want to address teeth you've never been happy with, improve your bite, or simply feel more confident about your smile.",
                            "If you've been putting off orthodontic treatment, your consultation is a good place to start."
                        ), 'link' => '/braces-for-adults/', 'link_label' => 'Braces for adults'],
                    ],
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Why Traditional Braces?', 'heading' => 'Sometimes, Tried-and-True Is *Exactly What You Need.*',
                    'intro' => 'There are several orthodontic treatment options available today. Traditional metal braces remain a widely used option because they give orthodontists direct control over tooth movement and can treat many different alignment and bite concerns.',
                    'items' => $cards(
                        ['shield', "They're attached to your teeth.", "You don't have to remember to put them in each day."],
                        ['sparkle', "They're designed for controlled movement.", 'Your orthodontist can make adjustments throughout treatment to guide your teeth.'],
                        ['users', "They're suitable for different ages.", 'Kids, teens, and adults may all be candidates for traditional braces.'],
                        ['palette', 'You can make them your own.', 'Colorful elastic bands give younger patients—and anyone who wants them—the opportunity to personalize their braces.'],
                        ['check', 'They can address more than crooked teeth.', 'Braces may be used as part of treatment for crowding, spacing, and various bite concerns.']
                    ),
                    'button' => 'Talk to an Orthodontist About Your Options'],

                ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'Your Traditional Braces Journey', 'heading' => 'Knowing What Comes Next Makes *Getting Started Easier.*',
                    'items' => $steps(
                        ['Your Free Consultation', "We'll evaluate your teeth and bite, listen to what you'd like to change, and discuss whether orthodontic treatment is right for you."],
                        ['Your Personalized Treatment Plan', 'If braces are recommended, your orthodontist will explain your treatment plan and available payment options.'],
                        ['Braces Placement', "Once you're ready to begin, your brackets and wires will be placed according to your treatment plan. Same-day braces may be available for eligible patients, depending on your appointment and treatment circumstances."],
                        ['Regular Orthodontic Visits', "You'll return for scheduled visits so your orthodontist can monitor your progress and make adjustments as needed."],
                        ['Braces Come Off', 'Once your treatment goals have been reached, your braces will be removed.'],
                        ['Retainers', "After active treatment, you'll enter the retention phase. Retainers help maintain the position of your teeth."]
                    ),
                    'button' => 'Start Your Journey'],

                ['_type' => 'pricing', 'tone' => 'navy', 'eyebrow' => 'How Much Do Traditional Braces Cost?', 'heading' => 'Traditional Braces From *$99/Month*',
                    'intro' => $lines(
                        'We believe understanding your payment options should be part of your orthodontic consultation—not something you have to figure out afterward.',
                        'At Ignite Orthodontics, traditional braces start at $99/month. We offer several ways to pay for treatment:'
                    ),
                    'items' => $pay(
                        'If you have orthodontic benefits, our team can help you understand how your insurance may apply to treatment.',
                        'Flexible financing options may allow you to spread your treatment cost into monthly payments.',
                        'Prefer to pay directly? We can discuss your treatment cost and available payment arrangements.'
                    ),
                    'note' => $lines('Your total treatment cost will depend on your individual treatment plan and payment arrangements.', 'Want to know what your options look like?'),
                    'button' => $free],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Start With a Conversation.', 'heading' => "You Don't Need to Figure Out *Your Treatment Alone.*",
                    'intro' => $lines(
                        "You don't need to know whether you need traditional braces, clear braces, or another treatment option before you contact us. That's our job.",
                        "During your consultation, we'll look at your smile, evaluate your orthodontic needs, and explain the options available to you.",
                        "If traditional braces are a good fit, we'll walk you through what treatment could look like. If another option makes more sense, we'll explain that too.",
                        "Your first step doesn't have to be complicated. Schedule your free consultation."
                    ),
                    'button' => $free,
                    'image' => '/assets/img/happy-family.jpg', 'image_alt' => 'Smiling family', 'image_side' => 'right'],

                ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'What Makes Ignite Orthodontics Different?', 'heading' => 'An Orthodontic Practice Built *Around Orthodontics.*',
                    'items' => $cards(
                        ['tooth', 'Orthodontics Only', "We don't split our focus between general dentistry and orthodontics. Orthodontics is all we do."],
                        ['badge', 'Board-Certified Orthodontists', 'Your treatment is overseen by board-certified orthodontists who specialize in orthodontic care.'],
                        ['wallet', 'Flexible Ways to Pay', 'Use insurance, financing, or out-of-pocket payment based on your circumstances.'],
                        ['calendar', 'Convenient Appointments', 'Evening and weekend appointments help make orthodontic care easier to fit into your life.'],
                        ['arrow', 'No Referral Needed', 'You can come directly to Ignite Orthodontics. No dentist referral is required.'],
                        ['pin', 'Multiple Locations', "Choose the Ignite Orthodontics location that's most convenient for you."],
                        ['screen', 'Modern Technology', 'Technology helps our team evaluate your orthodontic needs and plan your treatment.'],
                        ['users', 'Family-Friendly Experience', "From a child's first orthodontic visit to an adult's long-awaited smile transformation, we make orthodontic treatment approachable for the entire family."]
                    )],

                ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Frequently Asked Questions About *Traditional Braces*',
                    'items' => $faq(
                        ['How much do traditional braces cost?', $lines('Traditional braces at Ignite Orthodontics start at $99/month. Your actual treatment cost depends on your individual treatment plan and payment arrangements.', 'We accept insurance and offer financing and out-of-pocket payment options.')],
                        ['Are traditional braces good for adults?', 'Traditional braces can be an option for adults as well as children and teenagers. Your orthodontist will evaluate your teeth and bite to determine which treatment options are appropriate.'],
                        ['How long do you need traditional braces?', $lines('Treatment time varies from patient to patient. The length of treatment depends on factors such as your alignment, bite, treatment goals, and how your teeth respond to treatment.', 'Your orthodontist can provide a more personalized estimate during your consultation.')],
                        ['Do traditional braces hurt?', 'You may experience some soreness or pressure when your braces are first placed and after adjustments. This is generally temporary as your mouth adjusts to treatment.'],
                        ['Can I eat normally with traditional braces?', $lines('You can continue to enjoy many of your favorite foods, but some hard, sticky, or chewy foods should be avoided because they can damage brackets or wires.', 'Your orthodontic team will provide instructions on caring for your braces.')],
                        ['How do I take care of my teeth with braces?', $lines('Brushing and cleaning between your teeth are especially important during orthodontic treatment. Brackets and wires can create additional places where food and plaque can collect.', 'Your orthodontic team will show you how to care for your teeth and braces properly.')],
                        ['Can I choose the color of my braces?', 'Traditional braces often use small elastic bands to hold the wire in place. Depending on your treatment, you may be able to choose different colors during your appointments.'],
                        ['Can I get traditional braces on the same day as my consultation?', $lines('Same-day braces may be available for eligible patients, depending on your individual treatment needs and appointment availability.', 'Ask our team about same-day treatment when you schedule your consultation.')],
                        ['Do I need a referral to see an orthodontist?', 'No. You can schedule directly with Ignite Orthodontics without a referral from a general dentist.'],
                        ['What happens after my braces come off?', "After your braces are removed, you'll enter the retention phase of treatment. Retainers help maintain the position of your teeth after active orthodontic treatment."],
                        ['Are traditional braces my only option?', $lines('No. Ignite Orthodontics offers other orthodontic treatment options as well.', 'The best option depends on your orthodontic needs, treatment goals, and personal preferences.')]
                    )],

                ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Your First Step Is a Free Consultation.', 'heading' => 'Ready to *Get Started?*',
                    'intro' => $lines(
                        "Whether you're considering braces for yourself, your child, or your teenager, you don't have to figure everything out before you contact us.",
                        "We'll evaluate your smile, answer your questions, and help you understand what comes next."
                    ),
                    'price' => 'Traditional Braces From $99/Month',
                    'button' => $free, 'call' => 'yes'],
            ],
        ],

        /* ================================================================
           CERAMIC BRACES (moves under Types of Braces)
           ================================================================ */
        [
            'parent' => 'types-of-braces',
            'slug' => 'ceramic-braces',
            'from' => 'ceramic-braces',
            'title' => 'Ceramic Braces',
            'seo_title' => 'Ceramic Braces From $99/Month | Ignite Orthodontics',
            'description' => 'Ceramic braces from $99/month at Ignite Orthodontics: tooth-colored brackets for a more discreet look, planned by board-certified orthodontists.',
            'hero' => [
                'eyebrow' => 'Ceramic Braces',
                'heading' => 'Straighten Your Smile With Ceramic Braces That *Blend In With Your Teeth.*',
                'lead' => $lines(
                    'Ceramic braces give you the control of fixed orthodontic treatment with a more discreet appearance. They use tooth-colored or clear brackets designed to make your braces less noticeable while your teeth are being straightened.',
                    'At Ignite Orthodontics, orthodontics is all we do. Your treatment is planned and monitored by board-certified orthodontists who treat kids, teens, and adults.'
                ),
                'price' => 'From $99/month*',
                'price_note' => '*Monthly payment depends on your treatment plan, insurance coverage, and payment arrangement.',
                'button' => $free,
                'points' => $lines('Insurance accepted', 'Flexible financing', 'No referral required'),
                'image' => '/assets/img/IMG_20260814_125146.jpg',
                'image_alt' => 'Smile with tooth-colored ceramic braces',
            ],
            'blocks' => [
                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'A More Subtle Look', 'heading' => 'Want Braces Without Making Them *the Center of Attention?*',
                    'intro' => $lines(
                        "Maybe you want to straighten your teeth, but you're not excited about having obvious metal brackets every time you smile.",
                        "You're not alone. For many patients, especially teens and adults, the appearance of braces is an important part of the decision.",
                        'Ceramic braces offer another option.',
                        'Instead of traditional metal-colored brackets, ceramic braces use brackets designed to blend more naturally with your teeth. You still get a fixed orthodontic appliance, but with a more subtle appearance.',
                        "And because your braces stay in place throughout treatment, you don't have to remember to put them in or take them out.",
                        'If you want the control of fixed braces with a more discreet look, ceramic braces may be worth considering.'
                    ),
                    'button' => 'See If Ceramic Braces Are Right for You',
                    'image' => '/assets/img/invisalign-braces-scaled.jpg', 'image_alt' => 'Smiling patient with braces holding teeth models', 'image_side' => 'right'],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'The Basics', 'heading' => 'What Are *Ceramic Braces?*',
                    'intro' => $lines(
                        'Ceramic braces are a type of fixed orthodontic treatment that uses brackets attached to your teeth and an orthodontic wire to gradually move your teeth into better positions.',
                        'The brackets are made from a tooth-colored or clear ceramic material, which can make them less noticeable than traditional metal braces.',
                        'Like other fixed braces, ceramic braces are attached to your teeth throughout treatment and adjusted periodically by your orthodontic team.',
                        'Your orthodontist will determine whether ceramic braces are appropriate based on your teeth, bite, treatment goals, and individual needs.'
                    )],

                ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'What They Treat', 'heading' => 'What Can Ceramic Braces *Help Correct?*',
                    'intro' => $lines('Ceramic braces can be used to address many of the same orthodontic concerns treated with fixed braces.', 'Depending on your individual case, treatment may help correct:'),
                    'list' => $lines('Crowded teeth', 'Gaps between teeth', 'Crooked teeth', 'Overbite', 'Underbite', 'Crossbite', 'Misaligned teeth', 'Bite problems'),
                    'list_style' => 'check',
                    'outro' => $lines(
                        'The right treatment depends on your specific orthodontic needs.',
                        "That's why your first step isn't choosing between ceramic braces, metal braces, or clear aligners. It's finding out what treatment is appropriate for you."
                    )],

                ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'Teens & Adults', 'heading' => 'Ceramic Braces for *Teens & Adults*',
                    'intro' => 'Ceramic braces can be an option for patients who want fixed orthodontic treatment but prefer a less noticeable appearance.',
                    'items' => [
                        ['label' => 'Teens', 'title' => 'For Teens', 'text' => $lines(
                            'Teens may want to straighten their teeth without feeling like their braces dominate every photo, conversation, or social event.',
                            'Ceramic brackets offer a more subtle alternative to traditional metal brackets while providing fixed orthodontic treatment.'
                        ), 'link' => '/braces-for-teens/', 'link_label' => 'Braces for teens'],
                        ['label' => 'Adults', 'title' => 'For Adults', 'text' => $lines(
                            "If you're an adult considering orthodontic treatment, you may have concerns about how braces will fit into your professional and personal life.",
                            'Ceramic braces can provide a more discreet appearance while remaining fixed to your teeth throughout treatment.',
                            "Whether you're preparing for a major event, starting a new job, or simply ready to do something about your smile, ceramic braces can be one option to discuss with your orthodontist."
                        ), 'link' => '/braces-for-adults/', 'link_label' => 'Braces for adults'],
                    ],
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Benefits', 'heading' => 'Why Choose *Ceramic Braces?*',
                    'items' => $cards(
                        ['eye', 'A More Discreet Appearance', 'The tooth-colored or clear brackets are designed to blend with your natural teeth, making them less noticeable than traditional metal brackets.'],
                        ['shield', 'Fixed Throughout Treatment', "Unlike removable aligners, ceramic braces remain attached to your teeth throughout treatment. You don't need to remember to put them in or take them out."],
                        ['sparkle', 'A Proven Orthodontic Approach', 'Ceramic braces use brackets and wires to apply controlled forces to your teeth over time.'],
                        ['smile', 'No Need to Hide Your Smile', 'You can work, attend school, meet friends, take photos, and go about your day while your orthodontic treatment works toward your new smile.'],
                        ['heart', 'Personalized Treatment', 'Your orthodontist creates a treatment plan based on your teeth, bite, goals, and individual orthodontic needs.']
                    )],

                ['_type' => 'compare', 'tone' => 'tint', 'eyebrow' => 'Compare Your Options', 'heading' => 'Ceramic Braces vs. Metal Braces *vs. Invisalign*',
                    'intro' => $lines('Not sure which option makes sense for you?', "Here's a simple starting point."),
                    'columns' => $lines('Ceramic Braces', 'Traditional Metal Braces', 'Invisalign'),
                    'rows' => $rows(
                        ['Appearance', 'Less noticeable', 'More noticeable', 'Very discreet'],
                        ['Fixed to teeth', 'Yes', 'Yes', 'No'],
                        ['Removable', 'No', 'No', 'Yes'],
                        ['Uses brackets & wires', 'Yes', 'Yes', 'No'],
                        ['Can be used for many orthodontic concerns', 'Yes', 'Yes', 'Depends on the case'],
                        ['Requires consistent wear', 'No', 'No', 'Yes'],
                        ['Best starting point', 'Patients wanting fixed treatment with a more subtle appearance', 'Patients who want traditional fixed braces', 'Patients looking for removable, discreet aligners']
                    ),
                    'note' => $lines("There isn't one treatment that's right for everyone.", 'Your orthodontist can evaluate your teeth and explain which options are appropriate for your case.'),
                    'button' => 'Compare Your Options at a Free Consultation'],

                ['_type' => 'steps', 'tone' => 'white', 'eyebrow' => 'Your Treatment', 'heading' => 'What Is Treatment With *Ceramic Braces Like?*',
                    'intro' => $lines("Choosing ceramic braces doesn't mean you have to figure everything out yourself.", 'Your Ignite Orthodontics team will guide you through each stage.'),
                    'items' => $steps(
                        ['Start With a Free Consultation', "We'll evaluate your teeth and bite, discuss your goals, and determine whether orthodontic treatment is recommended."],
                        ['Build Your Treatment Plan', 'If ceramic braces are appropriate for you, your orthodontist will develop a treatment plan based on your individual needs.'],
                        ['Get Your Braces', 'The ceramic brackets are placed on your teeth and connected with orthodontic wires.'],
                        ['Attend Your Adjustment Visits', "You'll return periodically so your orthodontist can monitor your progress and make adjustments as needed."],
                        ['Complete Treatment', "Once your teeth have reached their planned positions, your braces will be removed and you'll move into the retention phase."]
                    )],

                ['_type' => 'pricing', 'tone' => 'navy', 'eyebrow' => 'How Much Do Ceramic Braces Cost?', 'heading' => 'Ceramic Braces From *$99/Month*',
                    'intro' => $lines(
                        'We believe orthodontic treatment should be easier to understand—and easier to plan for financially.',
                        'Ignite Orthodontics offers flexible ways to pay, including:'
                    ),
                    'items' => $pay(
                        'We accept insurance and can help you understand how your benefits may apply.',
                        'Flexible financing options may be available to help spread the cost of treatment over time.',
                        'You can also discuss out-of-pocket payment options with our team.'
                    ),
                    'note' => $lines('Your actual cost depends on your treatment plan, insurance coverage, and payment arrangement.', 'The best way to understand your options is to schedule a free consultation.'),
                    'button' => $free],

                ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Why Ignite Orthodontics for Ceramic Braces?', 'heading' => 'Orthodontics. *Nothing Else.*',
                    'intro' => $lines(
                        "At Ignite Orthodontics, orthodontics isn't one service among dozens. It's what we do.",
                        'Our practice is dedicated exclusively to orthodontic care, so every part of your experience is built around helping you achieve a healthier, straighter smile.'
                    ),
                    'items' => $cards(
                        ['badge', 'Board-Certified Orthodontists', 'Your treatment is planned and monitored by board-certified orthodontists.'],
                        ['users', 'Treatment for Kids, Teens & Adults', "Orthodontic treatment isn't just for one age group. We work with patients at different stages of life and tailor treatment to their individual needs."],
                        ['wallet', 'Flexible Payment Options', 'We offer insurance, financing, and out-of-pocket payment options to help make treatment easier to manage.'],
                        ['calendar', 'Convenient Scheduling', 'Evening and weekend appointments may be available, making it easier to fit orthodontic visits into your schedule.'],
                        ['pin', 'Multiple Locations', "Choose the Ignite Orthodontics location that's most convenient for you."],
                        ['arrow', 'No Referral Required', "You don't need to wait for a referral before taking the first step toward orthodontic treatment."]
                    )],

                ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Your Consultation', 'heading' => "You Don't Have to Decide Between Ceramic Braces and Invisalign *on Your Own*",
                    'intro' => $lines(
                        "Maybe you know you want straighter teeth, but you're not sure how you want to get there.",
                        "That's exactly what your consultation is for.",
                        "We'll look at your teeth and bite, listen to what matters to you, and explain the treatment options that may be appropriate for your situation.",
                        'You can ask questions about:'
                    ),
                    'list' => $lines('Ceramic braces', 'Traditional metal braces', 'Invisalign', 'Treatment length', 'Cost and monthly payments', 'Insurance', 'Financing', 'What treatment will be like'),
                    'list_style' => 'check',
                    'outro' => "You don't need to choose your treatment before you meet us.",
                    'button' => $free],

                ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Ceramic Braces *FAQs*',
                    'items' => $faq(
                        ['Are ceramic braces less noticeable than metal braces?', 'Yes. Ceramic braces use tooth-colored or clear brackets designed to blend more naturally with your teeth, making them less noticeable than traditional metal brackets.'],
                        ['Are ceramic braces as effective as metal braces?', "Ceramic braces are a type of fixed orthodontic treatment and can address many orthodontic concerns. Whether they're appropriate for your specific case depends on your teeth, bite, and treatment goals."],
                        ['Are ceramic braces good for adults?', "Ceramic braces can be an option for adults who want fixed orthodontic treatment with a more discreet appearance. Your orthodontist can determine whether they're appropriate for your case."],
                        ['Can teenagers get ceramic braces?', 'Yes. Ceramic braces can be considered for teens who want a less noticeable fixed orthodontic treatment option.'],
                        ['Can ceramic braces fix crowded teeth?', 'Ceramic braces may be used to address crowded teeth. Your orthodontist will evaluate the severity of crowding and determine which treatment options are appropriate.'],
                        ['Can ceramic braces fix an overbite?', 'Ceramic braces may be used to address certain bite problems, including overbite. Treatment depends on the specific characteristics of your bite.'],
                        ['How long do ceramic braces take?', $lines('Treatment time varies from patient to patient. It depends on factors such as your orthodontic concerns, treatment plan, and how your teeth respond to treatment.', 'Your orthodontist can give you a more specific estimate after evaluating your teeth.')],
                        ['Are ceramic braces removable?', 'No. Ceramic braces are fixed to your teeth throughout treatment. They are different from removable clear aligners such as Invisalign.'],
                        ['Can I eat normally with ceramic braces?', "You'll receive specific instructions about foods to avoid and how to care for your braces. Some foods can damage brackets or wires, so following your orthodontist's instructions is important."],
                        ['Do ceramic braces hurt?', 'You may experience some pressure, tenderness, or discomfort when braces are initially placed or adjusted. Your orthodontic team can explain what to expect and how to manage temporary discomfort.'],
                        ['How much do ceramic braces cost?', 'Ceramic braces are available at Ignite Orthodontics from $99/month. Your actual monthly payment and total treatment cost depend on your treatment plan, insurance coverage, and payment arrangement.'],
                        ['Do I need a referral to see an orthodontist?', 'No referral is required to schedule a consultation with Ignite Orthodontics.'],
                        ['Can I get ceramic braces the same day as my consultation?', 'Same-day treatment may be possible for eligible patients depending on individual circumstances and appointment availability. Ask our team about same-day options when scheduling your consultation.']
                    )],

                ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Find Out if Ceramic Braces Are Right for You', 'heading' => 'Ready for a *Straighter Smile?*',
                    'intro' => $lines(
                        "You don't have to live with crooked or crowded teeth forever.",
                        "And you don't have to choose between effective orthodontic treatment and a more discreet appearance without first understanding your options.",
                        'Meet with an Ignite Orthodontics team member, discuss your goals, and learn about the treatment options available for your smile.'
                    ),
                    'button' => $free, 'call' => 'yes'],
            ],
        ],
    ],
];
