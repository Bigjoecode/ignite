<?php
// Ad landing pages for Ignite Orthodontics Farmington Hills (/lp/farmingtonhills/...).
// Loaded with: php app/cli/import-pages.php app/data/content/lp-farmington-hills.php
// After import the pages are edited in /admin/pages/?type=lp.
//
// Three pages follow the structure of the practice's reference pages on
// visit.detroitdentalnow.com (confident smile, underbite, crowded teeth); the rest
// use Ignite's approved treatment copy. The references' statistics ("1,050+ smiles",
// "4.9 stars") and their Detroit patients' reviews are not Ignite's, so they are not
// used: highlights are facts the practice has confirmed, and reviews are the Ignite
// patient reviews the practice confirmed as genuine. {office} becomes "Farmington Hills".

$lines = static fn(string ...$items): string => implode("\n", $items);
$steps = static fn(array ...$pairs): array => array_map(static fn(array $p): array => ['title' => $p[0], 'text' => $p[1]], $pairs);
$cards = static fn(array ...$rows): array => array_map(static fn(array $p): array => ['icon' => $p[0], 'title' => $p[1], 'text' => $p[2]], $rows);
$faq   = static fn(array ...$pairs): array => array_map(static fn(array $p): array => ['q' => $p[0], 'a' => $p[1]], $pairs);
$rows  = static fn(array ...$rows): array => array_map(static fn(array $p): array => ['label' => $p[0], 'values' => implode("\n", array_slice($p, 1))], $rows);
$review = static fn(string $name, string $quote): array => ['name' => $name, 'quote' => $quote];

$book      = 'Book Your Free Consultation';
$priceNote = '*Subject to approval. Your monthly payment depends on your treatment plan, insurance coverage and payment arrangement.';
$points    = $lines('No-cost consultation', 'Flexible payment plans', 'No referral needed');

// genuine Ignite patient reviews (confirmed by the practice)
$rv = [
    'sarah'   => $review('Sarah M.', 'I avoided braces for years, but the team made everything simple. I always knew what was happening, and my bite feels completely different now.'),
    'jason'   => $review('Jason T.', 'My son just finished his braces treatment and the improvement is incredible. Every appointment was smooth and well organised.'),
    'michael' => $review('Michael D.', 'I was nervous about getting braces as an adult, but it turned out to be much easier than I expected. The results are worth it.'),
    'heather' => $review('Heather S.', 'My daughter felt comfortable from the very first visit. The staff were patient and kind throughout her entire treatment.'),
    'brandon' => $review('Brandon W.', 'The difference in my smile and bite is life-changing. I only wish I had started sooner.'),
];

$stats = static fn(array ...$items): array => ['_type' => 'stats', 'tone' => 'white', 'items' => array_map(
    static fn(array $s): array => ['value' => $s[0], 'label' => $s[1], 'text' => $s[2] ?? ''], $items
)];
$statFree  = ['Free', 'Consultation', 'An exam, your options and a clear plan, at no cost.'];
$stat99    = ['$99', 'Braces from, per month', 'Flexible payment plans. Subject to approval.'];
$statBoard = ['Board', 'Certified orthodontists', 'Every plan designed and monitored by a specialist.'];

$pricing = static fn(string $heading, string $intro, string $note = ''): array => [
    '_type' => 'pricing', 'tone' => 'navy', 'eyebrow' => 'Flexible Financing', 'heading' => $heading, 'intro' => $intro,
    'items' => [
        ['icon' => 'wallet', 'title' => 'Flexible Payment Plans', 'text' => 'Spread the cost of treatment into manageable monthly payments, with braces from $99/month. Subject to approval.'],
        ['icon' => 'shield', 'title' => 'Insurance Assistance', 'text' => 'We accept insurance and help you understand your orthodontic benefits, so you know what is covered before you start.'],
        ['icon' => 'check', 'title' => 'Clear, Upfront Pricing', 'text' => 'You get a clear breakdown of your treatment cost and monthly payment before you commit to anything.'],
    ],
    'note' => $note, 'button' => 'See Your Treatment Options',
];

$whyIgnite = ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'Why Ignite Orthodontics', 'heading' => 'Orthodontics. *Nothing Else.*',
    'intro' => 'At Ignite Orthodontics {office}, orthodontics is all we do, so every part of your care is built around getting you a healthier, straighter smile.',
    'items' => $cards(
        ['badge', 'Board-Certified Orthodontists', 'Your treatment is planned and monitored by board-certified orthodontists.'],
        ['tooth', 'Orthodontics Only', 'We focus exclusively on orthodontic care, from your first visit to your final retainer.'],
        ['heart', 'Personalized Treatment', 'Your plan is built around your teeth, your bite and your goals.'],
        ['wallet', 'Flexible Payment Options', 'Insurance, financing and out-of-pocket options, with braces from $99/month.'],
        ['arrow', 'No Referral Required', 'Book your consultation directly with our {office} team.'],
        ['calendar', 'Convenient Appointments', 'Evening and weekend appointments may be available to fit around work and school.']
    )];

$braceTypes = ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'Braces Options', 'heading' => 'Find the Right Braces *for Your Smile*',
    'intro' => 'We offer different types of braces to suit your needs, lifestyle and treatment goals.',
    'items' => [
        ['label' => 'Most Widely Used', 'title' => 'Metal Braces', 'text' => 'Strong, reliable and highly effective for a wide range of alignment and bite issues. A great option for children, teens and adults.', 'link' => '', 'link_label' => ''],
        ['label' => 'More Discreet', 'title' => 'Ceramic Braces', 'text' => 'Work in the same way as metal braces, with tooth-coloured brackets that blend more naturally with your smile.', 'link' => '', 'link_label' => ''],
        ['label' => 'Modern Clip System', 'title' => 'Self-Ligating Braces', 'text' => 'A clip system holds the wire in place instead of elastic bands, for smoother tooth movement and easier cleaning.', 'link' => '', 'link_label' => ''],
        ['label' => 'Behind the Teeth', 'title' => 'Lingual Braces', 'text' => 'Attached to the back surfaces of your teeth, so they are hidden from view when you smile. Your orthodontist will tell you whether they suit your bite.', 'link' => '', 'link_label' => ''],
    ]];

$bracesFaq = $faq(
    ['How long will I need braces?', 'Most treatments last between 12 and 24 months depending on your case. You will get a personal estimate at your consultation.'],
    ['Do braces hurt?', 'You may feel mild pressure after placement or adjustments, but it is temporary and manageable. Modern braces are far more comfortable than most people expect.'],
    ['Am I too old for braces?', 'Not at all. Braces are effective at any age, and many of our patients are adults.'],
    ['How much do braces cost?', 'Braces start from $99/month (subject to approval). Your exact cost depends on your treatment plan, and you get a clear breakdown before you start.'],
    ['Do I need a referral?', 'No. You can book your consultation directly with Ignite Orthodontics {office}.'],
    ['Will I need a retainer afterward?', 'Yes. Retainers are essential to maintain your results long-term.']
);

$finalCta = static fn(string $heading, string $text, string $button = 'Book Your Free Consultation'): array => [
    '_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Your Next Step', 'heading' => $heading, 'intro' => $text,
    'price' => '', 'button' => $button, 'call' => 'yes',
];

$page = static function (string $slug, string $title, string $description, array $hero, array $blocks, string $parent = 'farmingtonhills') {
    return ['parent' => $parent, 'slug' => $slug, 'title' => $title, 'description' => $description,
        'seo_title' => $title . ' | Ignite Orthodontics', 'hero' => $hero, 'blocks' => $blocks];
};

return [
    'type'     => 'lp',
    'template' => 'lp-landing',
    'settings' => ['office' => 'farmington-hills', 'index' => 'no'],
    'off'      => [],

    'pages' => [

        /* ================================================================
           /lp/farmingtonhills/ — braces in Farmington Hills
           ================================================================ */
        $page('farmingtonhills', 'Braces in Farmington Hills', 'Braces for kids, teens and adults at Ignite Orthodontics Farmington Hills. Free consultation, braces from $99/month.', [
            'eyebrow' => 'Orthodontist in {office}, MI',
            'heading' => 'Braces in {office} for *Kids, Teens and Adults*',
            'lead' => $lines(
                'Straighter teeth, a healthier bite and a smile you are proud of, planned and monitored by board-certified orthodontists at Ignite Orthodontics {office}.',
                'We make the process clear, comfortable and fully tailored to you, from your free consultation to the day your braces come off.'
            ),
            'benefits' => $lines('Free consultation and treatment plan', 'Metal, ceramic, self-ligating and lingual braces', 'Flexible payment plans, braces from $99/month*', 'No referral needed'),
            'price' => 'Braces from $99/month*', 'price_note' => $priceNote,
            'button' => $book, 'points' => $points,
            'image' => '/assets/img/IMG_20260814_125736.jpg', 'image_alt' => 'Smiling family outdoors',
        ], [
            $stats($statFree, $stat99, $statBoard),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Why Braces', 'heading' => 'Why Braces Still Remain *the Gold Standard*',
                'intro' => $lines(
                    'Braces offer precise, controlled movement of the teeth, making them highly effective for both simple and complex orthodontic concerns. They work continuously to guide your teeth into proper alignment, delivering predictable, long-lasting results.',
                    'Whether you are dealing with crowding, spacing, bite issues or overall misalignment, braces provide a structured and proven path to a healthier smile.'
                ),
                'button' => $book,
                'image' => '/assets/img/IMG_20260814_125146.jpg', 'image_alt' => 'Close-up of a smile with braces', 'image_side' => 'right'],
            $braceTypes,
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Who Braces Are For', 'heading' => 'Who Can Benefit *From Braces?*',
                'intro' => 'Braces can effectively treat a wide range of orthodontic concerns, including:',
                'list' => $lines('Crooked or rotated teeth', 'Crowding caused by lack of space', 'Gaps between teeth', 'Overbite', 'Underbite', 'Crossbite', 'Open bite', 'General alignment and spacing issues'),
                'list_style' => 'check',
                'outro' => 'Braces are suitable for children, teens and adults. Whether treatment starts early or later in life, the goal is the same: a healthier bite and a more confident smile.'],
            ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'How It Works', 'heading' => 'How the Braces *Process Works*',
                'items' => $steps(
                    ['Free Consultation', 'We assess your teeth, bite and smile goals to understand what you need.'],
                    ['Digital Assessment', 'Advanced imaging gives us a detailed view of your teeth and jaw alignment.'],
                    ['Personalised Plan', 'You get a clear explanation of your options, timeline and cost.'],
                    ['Braces Placement', 'Your braces are carefully fitted, and we guide you through what to expect.'],
                    ['Regular Adjustments', 'We monitor your progress and make small adjustments along the way.'],
                    ['Completion and Retainers', 'Retainers protect and maintain your new smile.']
                ),
                'button' => $book],
            $pricing('Braces From *$99/Month*', 'Orthodontic treatment is an investment, so we make it easier to plan for.', 'Payment plans are subject to approval.'),
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'Patient Stories', 'heading' => 'Real Patients. *Real Braces Results.*', 'items' => [$rv['sarah'], $rv['michael'], $rv['brandon']]],
            $whyIgnite,
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Braces Questions, *Answered*', 'items' => $bracesFaq],
            $finalCta('Your New Smile Starts in *{office}*', 'Take the first step toward a healthier, straighter smile with a free braces consultation at Ignite Orthodontics {office}.'),
        ], ''),

        /* ================================================================
           braces-kids
           ================================================================ */
        $page('braces-kids', 'Braces for Kids in Farmington Hills', 'Gentle braces and early orthodontic care for kids at Ignite Orthodontics Farmington Hills. Free consultation.', [
            'eyebrow' => 'Braces for Kids in {office}',
            'heading' => 'Healthy, Confident *Smiles for Kids*',
            'lead' => 'Gentle, fun and stress-free orthodontic care designed for children. We guide early jaw growth, fix crowding and help your child build a lifetime of confidence.',
            'benefits' => $lines('Early check-ups from age 7', 'Gentle, kid-friendly care', 'Colourful braces kids love', 'Insurance accepted and flexible payment plans'),
            'price' => 'Braces from $99/month*', 'price_note' => $priceNote,
            'button' => 'Book Your Child\'s Free Consultation', 'points' => $points,
            'image' => '/assets/img/istockphoto-1202583861-612x612-1.webp', 'image_alt' => 'Child at an orthodontic consultation',
        ], [
            $stats(['Age 7', 'First check-up', 'The age orthodontists recommend for a first evaluation.'], $statFree, $stat99),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Early Growth Guidance', 'heading' => 'Why a Check-Up *by Age 7?*',
                'intro' => $lines(
                    'By age 7, a child has a mix of baby teeth and adult teeth, which lets an orthodontist spot jaw and bite issues before they become complex problems.',
                    'Early treatment works with your child\'s natural growth. It can:'
                ),
                'list' => $lines('Guide jaw growth for proper facial structure', 'Create room for crowded, emerging adult teeth', 'Help correct thumb-sucking and tongue-thrust habits', 'Reduce the need to extract permanent teeth later', 'Make later treatment shorter and simpler'),
                'list_style' => 'check',
                'button' => 'Book Your Child\'s Free Consultation',
                'image' => '/assets/img/shutterstock_2331223617-min.jpg', 'image_alt' => 'Child smiling during an orthodontic exam', 'image_side' => 'right'],
            ['_type' => 'panels', 'tone' => 'tint', 'eyebrow' => 'Two Phases', 'heading' => 'Treatment That *Grows With Your Child*',
                'items' => [
                    ['label' => 'Phase 1 · Ages 7–10', 'title' => 'Preventative Guidance', 'text' => 'Focuses on jaw expansion and space maintenance while your child is actively growing.', 'link' => '', 'link_label' => ''],
                    ['label' => 'Phase 2 · Ages 11+', 'title' => 'Comprehensive Alignment', 'text' => 'Full braces or clear aligners once the permanent adult teeth have come in, to finalise alignment.', 'link' => '', 'link_label' => ''],
                ],
                'note' => 'Not every child needs early treatment. We will tell you honestly whether now is the right time.'],
            ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Tailored Treatments', 'heading' => 'Orthodontic Options *Designed for Kids*',
                'items' => $cards(
                    ['palette', 'Metal Braces', 'Durable, precise and fun. Kids love choosing coloured bands at every appointment.'],
                    ['eye', 'Clear Ceramic Braces', 'Blend in with the natural tooth colour for a more discreet look.'],
                    ['refresh', 'Invisalign® First', 'Removable clear aligners designed for growing children with a mix of baby and adult teeth.'],
                    ['sparkle', 'Palatal Expanders', 'Gentle appliances that widen a narrow upper jaw, easing crowding.']
                )],
            ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'Parent & Kid Friendly', 'heading' => 'A *Stress-Free* Journey',
                'items' => $steps(
                    ['Warm Welcome', 'Friendly staff and zero pressure. We explain everything in kid-friendly terms.'],
                    ['3D Digital Scans', 'No gooey impressions: a quick 3D scan creates a precise model of the teeth.'],
                    ['Personalised Plan', 'A clear roadmap with timelines, transparent pricing and payment options.'],
                    ['Pick Colour Bands', 'Your child can personalise their braces with favourite colours.']
                )],
            $pricing('Making Kids\' Braces *Affordable*', 'Every child deserves a healthy smile. We work with your insurance and offer low monthly payment options.', 'Payment plans are subject to approval.'),
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'From Parents', 'heading' => 'What *Parents* Say', 'items' => [$rv['heather'], $rv['jason']]],
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'Parent FAQs', 'heading' => 'Questions *Parents* Ask', 'items' => $faq(
                ['At what age should my child first see an orthodontist?', 'The American Association of Orthodontists recommends a first orthodontic evaluation by age 7, when early jaw problems and crowding can be spotted before permanent teeth fully come in.'],
                ['Does early treatment mean my child will not need braces later?', 'Early treatment prepares the jaw and makes room for permanent teeth. Many children still need a second phase later, but it is usually shorter and simpler.'],
                ['Do braces hurt?', 'Putting braces on does not hurt. Your child may feel mild pressure for a few days after adjustments; soft foods and over-the-counter pain relief help.'],
                ['Can my child still play sports or instruments?', 'Yes. We recommend a mouthguard for sports to protect the teeth and lips.'],
                ['How much do braces for kids cost?', 'It depends on your child\'s needs and treatment length. The consultation is free, we accept insurance, and payment plans start from $99/month (subject to approval).']
            )],
            $finalCta('Give Your Child a Smile *They Will Love*', 'Book a free consultation at Ignite Orthodontics {office}. We will check your child\'s teeth and bite and explain every option, with no pressure.', 'Book Your Child\'s Free Consultation'),
        ]),

        /* ================================================================
           braces-teens
           ================================================================ */
        $page('braces-teens', 'Braces for Teens in Farmington Hills', 'Braces for teens at Ignite Orthodontics Farmington Hills: metal, ceramic and Invisalign options, from $99/month. Free consultation.', [
            'eyebrow' => 'Braces for Teens in {office}',
            'heading' => 'Give Your Teen a Smile *They Can Feel Confident About*',
            'lead' => $lines(
                'Braces can straighten crooked teeth, improve bite problems and create a healthier-looking smile during the teen years.',
                'At Ignite Orthodontics {office}, your teen\'s treatment is planned and monitored by board-certified orthodontists who make the experience work for everyday teen life.'
            ),
            'benefits' => $lines('Metal, ceramic and Invisalign options', 'Colourful braces teens can make their own', 'Appointments that fit around school', 'Braces from $99/month*'),
            'price' => 'Braces from $99/month*', 'price_note' => $priceNote,
            'button' => 'Book Your Teen\'s Free Consultation', 'points' => $points,
            'image' => '/assets/img/home-page-hero-image-kids-ortho.jpg', 'image_alt' => 'Smiling teenager with braces',
        ], [
            $stats($statFree, $stat99, $statBoard),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Is It Time?', 'heading' => 'Your Teen\'s Smile Is Changing. *This Is a Good Time to Check In.*',
                'intro' => $lines('School. Friends. Sports. Photos. Prom. Graduation. Confidence.', 'Maybe you have noticed:'),
                'list' => $lines('Teeth that are crowded or overlapping', 'Large spaces between teeth', 'Teeth that stick out', 'An overbite or underbite', 'Difficulty biting or chewing', 'Your teen becoming self-conscious about their smile'),
                'list_style' => 'check',
                'outro' => 'A consultation gives you the answers. We will evaluate your teen\'s teeth and bite and walk you through the options.',
                'button' => 'Book Your Teen\'s Free Consultation',
                'image' => '/assets/img/istockphoto-2166449878-612x612-1.webp', 'image_alt' => 'Two smiling teenagers', 'image_side' => 'right'],
            ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'For Teens', 'heading' => 'It\'s Not Just About *a Straighter Smile*',
                'intro' => $lines('For parents, orthodontic treatment can feel like a medical decision. For teens, it is personal.', 'They may be thinking:'),
                'list' => $lines('"Will everyone notice my braces?"', '"Can I still play sports?"', '"How long will I have to wear them?"'),
                'list_style' => 'quote',
                'outro' => 'Those concerns matter. We make sure your teen understands their treatment and feels comfortable asking questions.'],
            ['_type' => 'panels', 'tone' => 'white', 'eyebrow' => 'Treatment Options', 'heading' => 'Braces Options *for Teens*',
                'items' => [
                    ['label' => 'Fixed', 'title' => 'Traditional Metal Braces', 'text' => 'A tried-and-true option, with colourful bands teens can change at every visit.', 'link' => '', 'link_label' => ''],
                    ['label' => 'Discreet', 'title' => 'Ceramic Braces', 'text' => 'Tooth-coloured brackets for a more subtle look while staying fixed to the teeth.', 'link' => '', 'link_label' => ''],
                    ['label' => 'Removable', 'title' => 'Invisalign', 'text' => 'Clear, removable aligners for teens who are good candidates and can wear them consistently.', 'link' => '', 'link_label' => ''],
                ]],
            ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'Life With Braces', 'heading' => 'Braces That Fit *Teen Life*',
                'items' => $cards(
                    ['ball', 'Play Sports', 'Teens can keep playing, with a mouthguard recommended for contact sports.'],
                    ['camera', 'Take Photos', 'Many teens choose band colours to make their braces part of their look.'],
                    ['book', 'Go to School', 'Braces do not stop school, activities or time with friends.'],
                    ['food', 'Enjoy Their Favourite Foods', 'With a few changes: we explain which foods to avoid.']
                )],
            ['_type' => 'steps', 'tone' => 'white', 'eyebrow' => 'Your First Visit', 'heading' => 'What Happens at a *Teen Consultation?*',
                'items' => $steps(
                    ['We Get to Know Your Teen', 'We talk about their smile, concerns and goals.'],
                    ['We Evaluate Teeth and Bite', 'An orthodontic evaluation shows whether treatment may be appropriate.'],
                    ['We Explain the Options', 'Clear options, and why a particular approach may suit your teen.'],
                    ['We Discuss Timing and Cost', 'Whether now is the right time, plus insurance and payment options.']
                ),
                'note' => 'No pressure. No confusing terminology. Just a clear conversation about your teen\'s smile.'],
            $pricing('Braces for Teens From *$99/Month*', 'We know orthodontic treatment is a significant investment for families.', 'Payment plans are subject to approval.'),
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'From Parents', 'heading' => 'What *Families* Say', 'items' => [$rv['jason'], $rv['heather']]],
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'What Parents *Want to Know*', 'items' => $faq(
                ['Will braces hurt my teen?', 'Your teen may feel some pressure or tenderness when braces are placed or adjusted. This is typically temporary.'],
                ['Can my teen play sports with braces?', 'Yes. We recommend appropriate protection, such as a mouthguard.'],
                ['Can my teen get Invisalign instead of braces?', 'Invisalign may be an option for some teens, depending on their teeth, bite and ability to wear the aligners as instructed.'],
                ['What if my teen does not want braces?', 'That is common. A consultation lets your teen ask questions and understand what treatment would really involve, with no pressure.'],
                ['Do we need a referral?', 'No. You can book a consultation directly with our {office} team.']
            )],
            $finalCta('Your Teen\'s Smile Starts With *One Conversation*', 'You do not need to know which braces your teen needs before you call. That is what the free consultation is for.', 'Book Your Teen\'s Free Consultation'),
        ]),

        /* ================================================================
           braces — general braces page (Google Ads)
           ================================================================ */
        $page('braces', 'Braces in Farmington Hills', 'Braces at Ignite Orthodontics Farmington Hills: board-certified orthodontists, free consultation, braces from $99/month.', [
            'eyebrow' => 'Braces in {office}, MI',
            'heading' => 'Straighter Teeth. Stronger Bite. *Lasting Results.*',
            'lead' => 'Braces are one of the most reliable ways to correct misaligned teeth, improve bite function and create a healthier, more confident smile. At Ignite Orthodontics {office}, we make the process clear, comfortable and tailored to you.',
            'benefits' => $lines('Free consultation and treatment plan', 'Board-certified orthodontists', 'Braces from $99/month*', 'Insurance accepted'),
            'price' => 'Braces from $99/month*', 'price_note' => $priceNote,
            'button' => $book, 'points' => $points,
            'image' => '/assets/img/IMG_20260814_125146.jpg', 'image_alt' => 'Smile with braces',
        ], [
            $stats($statFree, $stat99, $statBoard),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Is It Time?', 'heading' => 'Is It Time to Do Something *About Your Smile?*',
                'intro' => $lines('You may have gotten used to your teeth looking a certain way. You do not have to know exactly what treatment you need before contacting us.', 'You may be considering braces if you have:'),
                'list' => $lines('Crowded or overlapping teeth', 'Gaps or spaces between teeth', 'An overbite or underbite', 'A crossbite', 'Rotated or misaligned teeth', 'Concerns about the appearance of your smile'),
                'list_style' => 'check',
                'button' => 'Find Out If Braces Are Right for You',
                'image' => '/assets/img/IMG_20260814_125055.jpg', 'image_alt' => 'Smiling patient with braces', 'image_side' => 'right'],
            $braceTypes,
            ['_type' => 'compare', 'tone' => 'white', 'eyebrow' => 'Compare Your Options', 'heading' => 'Braces vs. *Clear Aligners*',
                'intro' => 'Both can straighten teeth, but they work differently. We will help you choose at your consultation.',
                'columns' => $lines('Braces', 'Clear Aligners'),
                'rows' => $rows(
                    ['Fixed or removable', 'Fixed brackets and wires', 'Removable trays'],
                    ['Remembering to wear', 'Not needed, they stay in place', 'Must be worn consistently'],
                    ['Cases treated', 'A wide range of orthodontic problems', 'Certain orthodontic cases'],
                    ['Appearance', 'Metal, ceramic or behind the teeth', 'Less noticeable']
                )],
            ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'Your Braces Journey', 'heading' => 'Knowing What Comes Next *Makes It Easier*',
                'items' => $steps(
                    ['Free Consultation', 'We evaluate your teeth and bite and discuss your goals.'],
                    ['Personalised Plan', 'Your orthodontist explains the plan and payment options.'],
                    ['Braces Placement', 'Same-day braces may be available for eligible patients.'],
                    ['Regular Visits', 'We monitor your progress and adjust as needed.'],
                    ['Braces Come Off', 'Once your goals are reached, your braces are removed.'],
                    ['Retainers', 'Retainers keep your teeth in their new positions.']
                ),
                'button' => $book],
            $pricing('Braces From *$99/Month*', 'Understanding your payment options should be part of your consultation, not something you figure out afterward.', 'Payment plans are subject to approval.'),
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'Patient Stories', 'heading' => 'Real Patients. *Real Results.*', 'items' => [$rv['sarah'], $rv['michael'], $rv['brandon']]],
            $whyIgnite,
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Braces Questions, *Answered*', 'items' => $bracesFaq],
            $finalCta('Ready to Start Your *Braces Journey?*', 'Book a free consultation at Ignite Orthodontics {office}. We will guide you through your options and create a plan that fits your goals.'),
        ]),

        /* ================================================================
           braces-99 — the $99/month offer (confident-smile structure)
           ================================================================ */
        $page('braces-99', 'Braces From $99/Month in Farmington Hills', 'Braces from $99/month at Ignite Orthodontics Farmington Hills. Free consultation, flexible payment plans, insurance accepted.', [
            'eyebrow' => 'Braces Offer · {office}',
            'heading' => 'Braces From *$99/Month* in {office}',
            'lead' => 'Stop putting off the smile you want. Get braces from board-certified orthodontists with flexible monthly payments, and start with a free consultation.',
            'benefits' => $lines('Braces from $99/month*', 'Free consultation and treatment plan', 'Insurance accepted', 'Straighter smile that boosts confidence'),
            'price' => 'Braces from $99/month*', 'price_note' => $priceNote,
            'button' => 'Claim Your Free Consultation', 'points' => $points,
            'image' => '/assets/img/happy-family.jpg', 'image_alt' => 'Smiling family',
        ], [
            $stats($stat99, $statFree, $statBoard),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'The Truth About Braces Today', 'heading' => 'Tired of *Hiding Your Smile?*',
                'intro' => $lines('Many people put off braces for years, often because of concerns about comfort, cost or how long treatment might take.', 'You may have thought things like:'),
                'list' => $lines('"Braces will probably be uncomfortable."', '"It\'s going to take forever."', '"It\'s probably too expensive."', '"I\'m too old for braces."'),
                'list_style' => 'quote',
                'outro' => 'With braces from $99/month, cost does not have to be the reason you wait. And the confidence that comes with a straight smile stays with you for life.',
                'button' => 'Claim Your Free Consultation',
                'image' => '/assets/img/IMG_20260814_125055.jpg', 'image_alt' => 'Smiling patient with braces', 'image_side' => 'left'],
            $pricing('A Smile Transformation *That Fits Your Budget*', $lines('Cost is one of the biggest reasons people delay orthodontic treatment. In reality, there are several ways we make it manageable.', 'We work with patients to provide:'), 'Braces from $99/month are subject to approval. Our team walks you through every option clearly before you commit.'),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Life After Braces', 'heading' => 'Straighter Teeth That *Transform Your Confidence*',
                'intro' => $lines('A straight smile does more than improve appearance. It changes how you feel every day.', 'Many patients tell us that after braces, they finally feel comfortable:'),
                'list' => $lines('Smiling in photos', 'Laughing without covering their mouth', 'Speaking confidently at work', 'Meeting new people'),
                'list_style' => 'check',
                'button' => 'Start Your Smile Transformation',
                'image' => '/assets/img/IMG_20260814_125736.jpg', 'image_alt' => 'Smiling family outdoors', 'image_side' => 'right'],
            ['_type' => 'compare', 'tone' => 'tint', 'eyebrow' => 'Why Choose Us', 'heading' => 'Why Patients Choose *Ignite Orthodontics*',
                'columns' => $lines('Ignite Orthodontics', 'Typical Experience Elsewhere'),
                'rows' => $rows(
                    ['Who treats you', 'Board-certified orthodontists', 'Not always an orthodontic specialist'],
                    ['Treatment planning', 'Personalised to your teeth and bite', 'One-size-fits-all solutions'],
                    ['Payment options', 'Braces from $99/month, insurance accepted', 'Limited financing choices'],
                    ['Patient experience', 'Friendly, unhurried orthodontic team', 'Rushed appointments']
                ),
                'note' => 'Our focus is helping you achieve a smile you feel proud to show.'],
            ['_type' => 'steps', 'tone' => 'white', 'eyebrow' => 'Simple Process', 'heading' => 'How *It Works*',
                'items' => $steps(
                    ['Book Your Free Consultation', 'We evaluate your smile and discuss your goals.'],
                    ['Get Your Personal Plan', 'A braces plan for your teeth and bite, with your monthly payment explained.'],
                    ['Start Your Transformation', 'We guide you through every step so progress stays smooth.']
                )],
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'Patient Stories', 'heading' => 'What Our *Patients Say*', 'items' => [$rv['brandon'], $rv['sarah'], $rv['michael']]],
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'Got Questions?', 'heading' => 'Your Questions About *Braces From $99/Month*', 'items' => $faq(
                ['Who qualifies for braces from $99/month?', 'Payment plans are subject to approval. Your monthly payment depends on your treatment plan, insurance coverage and payment arrangement; we explain it clearly at your free consultation.'],
                ['Is the consultation really free?', 'Yes. Your consultation, including an exam and treatment plan, is at no cost and with no obligation.'],
                ['Do you accept insurance?', 'Yes. We help you understand your orthodontic benefits and what they cover.'],
                ['Am I too old for braces?', 'Not at all. Many adults choose braces to improve their smile and confidence.'],
                ['How long does treatment take?', 'Most treatments last between 12 and 24 months. You get a personal estimate at your consultation.'],
                ['Do I need a referral?', 'No referral is required. You can book directly.']
            )],
            $finalCta('Your Confident Smile *Starts Here*', 'If you have been thinking about braces for years, this is the time to explore your options, with braces from $99/month and a free consultation at Ignite Orthodontics {office}.', 'Claim Your Free Consultation'),
        ]),

        /* ================================================================
           braces-confident-smiles (reference: braces-confidential-smile)
           ================================================================ */
        $page('braces-confident-smiles', 'Braces for a Confident Smile in Farmington Hills', 'Get braces for a confident smile at Ignite Orthodontics Farmington Hills. Board-certified orthodontists, free consultation.', [
            'eyebrow' => 'Orthodontist in {office}, MI',
            'heading' => 'Get Braces for a Confident Smile, *So You Can Smile Without Hiding*',
            'lead' => 'Stop hiding your smile in photos. Stop covering your mouth when you laugh. Our orthodontic team helps teens and adults achieve straight, confident smiles with modern braces designed for comfort and real results.',
            'benefits' => $lines('Straighter smile that boosts confidence', 'Flexible payment plans available', 'Comfortable, modern braces', 'Board-certified orthodontists'),
            'price' => '', 'price_note' => '',
            'button' => 'Book Your Free Braces Consultation', 'points' => $points,
            'image' => '/assets/img/IMG_20260814_125055.jpg', 'image_alt' => 'Happy patient with braces',
        ], [
            $stats($statFree, $statBoard, $stat99),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'The Truth About Braces Today', 'heading' => 'Tired of *Hiding Your Smile?*',
                'intro' => $lines('Many people put off getting braces for years. Sometimes it is because of concerns about comfort, cost or how long the treatment might take.', 'You may have thought things like:'),
                'list' => $lines('"Braces will probably be uncomfortable."', '"It\'s going to take forever."', '"It\'s probably too expensive."', '"I\'m too old for braces."'),
                'list_style' => 'quote',
                'outro' => 'And the confidence that comes with a straight smile? That stays with you for life.',
                'button' => 'Book Your Free Braces Consultation',
                'image' => '/assets/img/invisalign-braces-scaled.jpg', 'image_alt' => 'Smiling patient holding teeth models', 'image_side' => 'left'],
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'Patient Stories', 'heading' => 'What Our *Patients Are Saying*',
                'intro' => 'Many of our patients tell us the same thing after treatment: they wish they had started sooner.',
                'items' => [$rv['brandon'], $rv['sarah'], $rv['michael']]],
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Life After Braces', 'heading' => 'Straighter Teeth That *Transform Your Confidence*',
                'intro' => $lines('A straight smile does more than improve appearance. It changes how you feel every day.', 'Many patients tell us that after braces, they finally feel comfortable:'),
                'list' => $lines('Smiling in photos', 'Laughing without covering their mouth', 'Speaking confidently at work', 'Meeting new people without feeling self-conscious'),
                'list_style' => 'check',
                'outro' => 'Braces are not just about straight teeth. They are about feeling comfortable being yourself again.',
                'button' => 'Start Your Smile Transformation',
                'image' => '/assets/img/IMG_20260814_125736.jpg', 'image_alt' => 'Smiling family outdoors', 'image_side' => 'right'],
            ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Modern Orthodontics', 'heading' => 'Modern Braces *Designed for Comfort*',
                'intro' => $lines(
                    'Many people imagine braces the way they were years ago: bulky, uncomfortable and difficult to manage.',
                    'Orthodontics has improved significantly. Today\'s braces are more comfortable, more efficient and easier to live with, and your plan is tailored to your smile so your teeth move safely and steadily toward the result you want.'
                ),
                'button' => 'Schedule Your Consultation',
                'image' => '/assets/img/IMG_20260814_125146.jpg', 'image_alt' => 'Close-up of modern braces', 'image_side' => 'left'],
            $pricing('A Smile Transformation *That Fits Your Budget*', 'Cost is one of the biggest reasons people delay orthodontic treatment. We work with patients to make it manageable.', 'Everyone deserves to feel proud of their smile. Payment plans are subject to approval.'),
            ['_type' => 'compare', 'tone' => 'white', 'eyebrow' => 'Why Choose Us', 'heading' => 'Why Patients Choose *Our Braces Treatment*',
                'columns' => $lines('Ignite Orthodontics', 'Typical Experience Elsewhere'),
                'rows' => $rows(
                    ['Treatment approach', 'Modern orthodontic techniques', 'Outdated treatment approaches'],
                    ['Treatment planning', 'Personalised treatment planning', 'One-size-fits-all solutions'],
                    ['Payment options', 'Flexible payment options', 'Limited financing choices'],
                    ['Patient experience', 'Friendly, supportive orthodontic team', 'Rushed appointments'],
                    ['Who treats you', 'Board-certified orthodontists', 'Not always an orthodontic specialist']
                )],
            ['_type' => 'steps', 'tone' => 'tint', 'eyebrow' => 'Simple Process', 'heading' => 'How *It Works*',
                'intro' => 'Starting orthodontic treatment is easier than most people expect.',
                'items' => $steps(
                    ['Schedule Your Consultation', 'A full evaluation of your smile and a conversation about your goals.'],
                    ['Personalised Treatment Plan', 'A braces plan designed specifically for your teeth and bite.'],
                    ['Start Your Smile Transformation', 'We guide you through every step so progress stays smooth and predictable.']
                )],
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'Got Questions?', 'heading' => 'Your Questions About Braces, *Answered*', 'items' => $faq(
                ['Am I too old for braces?', 'Not at all. Many adults choose braces to improve their smile and confidence.'],
                ['Do braces hurt?', 'Modern braces are designed to be significantly more comfortable than older systems. Mild pressure after adjustments is temporary.'],
                ['How long does treatment take?', 'Most treatments last between 12 and 24 months, and you get a personal estimate at your consultation.'],
                ['Are payment plans available?', 'Yes. Flexible payment options are available, with braces from $99/month. Financing is subject to approval.'],
                ['Do I need a referral?', 'No referral is required. You can schedule your consultation directly.']
            )],
            ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Our Commitment', 'heading' => 'Trusted *Orthodontic Care*',
                'intro' => 'Patients trust Ignite Orthodontics {office} because we focus on:',
                'list' => $lines('Honest treatment recommendations', 'Modern orthodontic techniques', 'A comfortable patient experience', 'Care from board-certified orthodontists'),
                'list_style' => 'check',
                'outro' => 'When you visit us, our goal is simple: help you understand your options and create a plan that works for you.',
                'button' => $book],
            $finalCta('Your Confident Smile *Starts Here*', 'If you have been thinking about braces for years, this is the perfect time to explore your options. We will evaluate your smile, explain your treatment options and help you decide the best path forward.', 'Book Your Free Braces Consultation'),
        ]),

        /* ================================================================
           braces-underbite (reference: underbite-treatment)
           ================================================================ */
        $page('braces-underbite', 'Underbite Treatment in Farmington Hills', 'Underbite treatment for teens and adults at Ignite Orthodontics Farmington Hills. Free consultation, flexible payment plans.', [
            'eyebrow' => 'Underbite Treatment in {office}',
            'heading' => 'Fix Your Underbite So You Can *Eat Comfortably and Smile With Confidence Again*',
            'lead' => 'If your bite feels off, your teeth do not align properly, or your smile makes you feel self-conscious, our team helps teens and adults correct underbites with treatment designed to improve comfort, function and long-term confidence.',
            'benefits' => $lines('Improve bite alignment and jaw function', 'Reduce strain on teeth and jaw', 'Create a more balanced, natural smile', 'Modern orthodontic options available', 'Flexible payment plans to fit your budget'),
            'price' => '', 'price_note' => '',
            'button' => 'Book Your Free Consultation', 'points' => $points,
            'image' => '/assets/img/invisalign-braces-scaled.jpg', 'image_alt' => 'Smiling patient holding teeth models',
        ], [
            $stats($statFree, $statBoard, ['Teens & Adults', 'Treated', 'It is never too late to correct your bite.']),
            ['_type' => 'cards', 'tone' => 'white', 'eyebrow' => 'Everyday Impact', 'heading' => 'Living With an Underbite *Affects More Than You Think*',
                'intro' => $lines('An underbite is not just about how your teeth look. Over time, it can affect how you eat, how your jaw feels and how comfortable you are in everyday interactions.', 'You may start to notice:'),
                'items' => $cards(
                    ['food', 'Difficulty chewing', 'Certain foods become harder to bite and chew properly.'],
                    ['bite', 'Jaw tension', 'Tension or discomfort in the jaw throughout the day.'],
                    ['tooth', 'Uneven tooth wear', 'Teeth that meet unevenly can wear down over time.'],
                    ['chat', 'Feeling self-conscious', 'Hesitating when you smile or speak.']
                ),
                'note' => 'Most people learn to live with it, even when it causes daily frustration. That does not mean you have to keep adjusting.'],
            ['_type' => 'text', 'tone' => 'tint', 'eyebrow' => 'Understanding Your Bite', 'heading' => 'What Is an *Underbite?*',
                'intro' => $lines(
                    'An underbite happens when your lower teeth sit in front of your upper teeth when your mouth is closed. It can be caused by the position of your jaw, your teeth, or a combination of both.',
                    'The important thing to know is that underbites can often be corrected with the right treatment plan, and the earlier you understand your options, the easier it is to move forward with confidence.'
                ),
                'button' => 'Book Your Free Consultation',
                'image' => '/assets/img/IMG_20260814_125716.jpg', 'image_alt' => 'Braces model next to a clear aligner', 'image_side' => 'right'],
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'Your Options', 'heading' => 'Underbite *Treatment Options*',
                'intro' => 'Treatment depends on how your teeth and jaw are aligned, which is why every plan is customized. Your options may include:',
                'list' => $lines('Braces to gradually move teeth into proper alignment', 'Clear aligners for a more discreet option, in suitable cases', 'Bite correction techniques to improve jaw positioning', 'Comprehensive orthodontic care for more complex cases'),
                'list_style' => 'check',
                'outro' => 'We will walk you through what makes the most sense for your situation, clearly and without pressure.',
                'image' => '/assets/img/IMG_20260814_125146.jpg', 'image_alt' => 'Close-up of a smile with braces', 'image_side' => 'left'],
            ['_type' => 'reviews', 'tone' => 'tint', 'eyebrow' => 'Patient Stories', 'heading' => 'What Our *Patients Are Saying*', 'items' => [$rv['sarah'], $rv['brandon'], $rv['michael']]],
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'After Treatment', 'heading' => 'What Changes After *Underbite Treatment*',
                'intro' => $lines('Most patients expect the biggest difference to be how their smile looks. What stands out more is how much better everything feels.', 'After treatment, many patients notice:'),
                'list' => $lines('Easier, more comfortable chewing', 'Less tension in the jaw', 'A more natural bite and alignment', 'Increased confidence when smiling and speaking'),
                'list_style' => 'check',
                'outro' => 'It is not just about appearance. It is about feeling comfortable in your own smile again.'],
            $pricing('Concerned About *Cost?*', 'Orthodontic treatment can feel like a big decision, especially when you are unsure about pricing. We make it easier.', 'Our goal is to make treatment accessible without adding stress. Payment plans are subject to approval.'),
            ['_type' => 'steps', 'tone' => 'white', 'eyebrow' => 'Simple Process', 'heading' => 'Simple, *Step-by-Step* Process',
                'items' => $steps(
                    ['Consultation', 'We evaluate your bite, teeth and overall alignment.'],
                    ['Personalized Plan', 'A treatment plan created specifically for your needs.'],
                    ['Begin Treatment', 'We start your orthodontic care with clear guidance.'],
                    ['Ongoing Adjustments', 'We monitor progress and adjust as needed for the best results.']
                ),
                'note' => 'You will always know what to expect at each stage.'],
            ['_type' => 'cta', 'tone' => 'navy', 'eyebrow' => 'Take the First Step', 'heading' => 'You Don\'t Have to Keep Living *With an Underbite*',
                'intro' => 'If your bite has been bothering you for years, you are not alone. Taking the first step can make a lasting difference in how you eat, speak and feel every day.',
                'price' => '', 'button' => 'Book Your Free Consultation', 'call' => 'yes'],
            ['_type' => 'faq', 'tone' => 'white', 'eyebrow' => 'FAQ', 'heading' => 'Frequently Asked *Questions*', 'items' => $faq(
                ['Am I too old to fix an underbite?', 'No. Many adults successfully correct underbites with modern orthodontic treatment.'],
                ['How long does treatment take?', 'It depends on the severity of the case. Most orthodontic treatment lasts between 12 and 24 months, and you get a personal estimate at your consultation.'],
                ['Will treatment be uncomfortable?', 'Modern orthodontic options are designed to be more comfortable than older methods. Mild pressure after adjustments is temporary.'],
                ['Are clear aligners an option?', 'In some cases, yes. We will evaluate your bite and recommend the best approach.'],
                ['Do I need a referral?', 'No. You can book your consultation directly with our {office} team.']
            )],
        ]),

        /* ================================================================
           braces-crowded-teeth (reference: braces-crowded-teeth)
           ================================================================ */
        $page('braces-crowded-teeth', 'Braces for Crowded Teeth in Farmington Hills', 'Fix crowded teeth with braces at Ignite Orthodontics Farmington Hills. Free consultation, braces from $99/month.', [
            'eyebrow' => 'Crowded Teeth Treatment in {office}',
            'heading' => 'Fix Your Crowded Teeth With Braces So You Can *Smile With Confidence Again*',
            'lead' => 'Crowded teeth do not fix themselves. Our board-certified orthodontists straighten crowded teeth with comfortable, modern braces and a plan built around your smile.',
            'benefits' => $lines('Straighten crowded teeth effectively', 'Boost your confidence in photos and conversations', 'Comfortable, modern braces options', 'Flexible payment plans available'),
            'price' => 'Braces from $99/month*', 'price_note' => $priceNote,
            'button' => 'Book Your FREE Braces Consultation', 'points' => $lines('Takes about a minute', 'No pressure', 'No commitment'),
            'image' => '/assets/img/IMG_20260814_125131.jpg', 'image_alt' => 'Close-up of teeth with braces',
        ], [
            $stats($statFree, $statBoard, $stat99),
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'The Problem', 'heading' => 'Tired of Dealing With *Crowded Teeth?*',
                'intro' => $lines('Crowded teeth do not just affect how your smile looks; they affect how you feel every day.', 'You might find yourself:'),
                'list' => $lines('Hiding your smile in photos', 'Feeling self-conscious when talking', 'Avoiding close-up conversations', 'Struggling to clean between overlapping teeth'),
                'list_style' => 'check',
                'outro' => 'And if you have already been told you need braces, you already know it will not fix itself.',
                'image' => '/assets/img/IMG_20260814_125146.jpg', 'image_alt' => 'Close-up of a smile', 'image_side' => 'left'],
            ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'The Good News', 'heading' => 'It\'s Easier *Than You Think*',
                'intro' => 'We help patients fix crowded teeth with braces that are:',
                'items' => $cards(
                    ['heart', 'Designed for comfort', 'Modern materials and techniques make for a smoother experience throughout your journey.'],
                    ['sparkle', 'Customized for your smile', 'Whether your crowding is mild or more advanced, your plan is tailored to you.'],
                    ['shield', 'Built for lasting results', 'We focus on long-term stability, with retainers to keep your new smile in place.']
                )],
            ['_type' => 'cards', 'tone' => 'navy', 'eyebrow' => 'Why Ignite', 'heading' => 'Why Patients Choose *Ignite Orthodontics*',
                'items' => $cards(
                    ['badge', 'Board-certified orthodontists', 'Your treatment is planned and monitored by a specialist.'],
                    ['screen', 'Modern braces technology', 'Comfortable, efficient treatment options.'],
                    ['wallet', 'Transparent pricing', 'Braces from $99/month, with flexible payments and insurance accepted.'],
                    ['users', 'A friendly, judgment-free team', 'No pressure and no lectures, just clear answers.']
                )],
            ['_type' => 'reviews', 'tone' => 'white', 'eyebrow' => 'Patient Stories', 'heading' => 'Hear From Patients Who *Finally Fixed Their Smile*', 'items' => [$rv['sarah'], $rv['michael'], $rv['brandon']]],
            ['_type' => 'cards', 'tone' => 'tint', 'eyebrow' => 'Common Worries', 'heading' => 'What\'s *Holding You Back?*',
                'items' => $cards(
                    ['clock', '"I\'ve waited this long… does it even matter?"', 'Yes. Your confidence does not have an expiration date. It is never too late to feel great about your smile.'],
                    ['wallet', '"What if it\'s too expensive?"', 'We create a payment plan that works for your budget, with braces from $99/month (subject to approval).'],
                    ['heart', '"What if it\'s uncomfortable?"', 'Modern braces are far more comfortable than most people expect.']
                )],
            ['_type' => 'cta', 'tone' => 'orange', 'eyebrow' => 'Take the First Step', 'heading' => 'You\'ve Been Thinking About It… *Now Take the First Step*',
                'intro' => 'If your teeth are crowded today, they will likely be the same a year from now, unless you take action. The sooner you start, the sooner you can enjoy a straighter smile.',
                'price' => '', 'button' => 'Schedule Now', 'call' => 'yes'],
            ['_type' => 'text', 'tone' => 'white', 'eyebrow' => 'A Year From Now', 'heading' => 'Your Teeth Will Be the Same, *Or Better*',
                'intro' => 'The only difference is whether you take action today. Many patients delayed it too, until they did not. Take control of your confidence today.',
                'button' => 'Book Consultation',
                'image' => '/assets/img/IMG_20260814_125055.jpg', 'image_alt' => 'Smiling patient with braces', 'image_side' => 'right'],
            ['_type' => 'faq', 'tone' => 'tint', 'eyebrow' => 'FAQ', 'heading' => 'Braces for *Crowded Teeth*', 'items' => $faq(
                ['Do I really need braces for crowded teeth?', 'If your teeth are overlapping, difficult to clean or affecting your bite, braces are often the most effective way to correct it and prevent long-term problems.'],
                ['Are braces painful?', 'Most patients feel mild discomfort for a few days after adjustments, but modern braces are much more comfortable than older options.'],
                ['How long will I need to wear braces?', 'Most treatments last between 12 and 24 months depending on how severe the crowding is. You get a clear timeline at your consultation.'],
                ['Am I too old to get braces?', 'Not at all. Many of our patients are adults finally fixing their smile.'],
                ['How much do braces cost?', 'Costs vary with your needs. Braces start from $99/month (subject to approval), and you get a clear breakdown before starting.'],
                ['Can I still eat normally with braces?', 'Yes, with a few adjustments. You will avoid very hard or sticky foods, but most meals are not affected.'],
                ['What types of braces do you offer?', 'Metal, ceramic, self-ligating and lingual braces, as well as clear aligners. We recommend the best option at your consultation.'],
                ['How do I know if I\'m a good candidate?', 'The best way is a free consultation. We evaluate your teeth and tell you exactly what is needed, with no guesswork.']
            )],
            $finalCta('Still Unsure? *That\'s Okay.*', 'Start with a free consultation. No pressure. Just answers.', 'Schedule Your Visit Today'),
        ]),
    ],
];
