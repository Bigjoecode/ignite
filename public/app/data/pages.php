<?php
// Starter content for the secondary pages. Source of truth until the admin CMS
// moves pages into MySQL. 'draft' pages are live but noindex and left out of
// the sitemap until real content replaces the starter copy.
//
// Fields: title, description, kicker, h1 (may contain <em>), lead,
//         sections [[heading, paragraph], ...], links [[href, label, text], ...],
//         note (highlighted notice), finder (show office list), draft
return [
    'about-us' => [
        'title' => 'About Us', 'description' => 'Ignite Orthodontics provides braces and clear aligners for children, teens and adults across our Michigan offices.',
        'kicker' => 'About Ignite Orthodontics', 'h1' => 'Orthodontic Care <em>Built Around You.</em>',
        'lead' => 'Choosing an orthodontist is about more than straightening teeth. It is about finding a team that listens, explains your options clearly, and makes treatment work with your life.',
        'sections' => [
            ['Specialist care, explained clearly', 'From your first consultation through the final stages of treatment, our goal is to make your experience clear, comfortable and easier to manage. We walk you through every option before you decide.'],
            ['Treatment for every age', 'We treat children, teens and adults with traditional metal braces, ceramic braces, gold braces and clear aligners, so you can choose an approach that fits your needs and lifestyle.'],
            ['Care that fits your schedule and budget', 'We offer convenient appointment times, work with many dental insurance plans, and provide flexible payment options to help spread the cost of treatment.'],
        ],
        'finder' => true,
    ],
    'treatments' => [
        'title' => 'Our Treatments', 'description' => 'Metal braces, ceramic braces, gold braces and clear aligners for kids, teens and adults at Ignite Orthodontics.',
        'kicker' => 'Orthodontic Treatment Options', 'h1' => 'A Smile Is <em>Timeless.</em> Your Treatment Should Fit You.',
        'lead' => 'There is no one right way to straighten every smile. We offer proven treatment options for children, teens and adults.',
        'links' => [
            ['/braces-for-kids/', 'Braces for Kids', 'Early evaluations and gentle treatment that guides growing smiles.'],
            ['/braces-for-teens/', 'Braces for Teens', 'Metal, ceramic or clear options that fit school, sports and social life.'],
            ['/braces-for-adults/', 'Braces for Adults', 'Discreet treatment that fits around work and everyday life.'],
            ['/types-of-braces/', 'Types of Braces', 'Compare metal, gold and ceramic braces side by side.'],
            ['/clear-aligners/', 'Clear Aligners', 'Removable, virtually invisible trays that straighten teeth gradually.'],
            ['/invisalign/', 'Invisalign', 'A well-known clear aligner system for teens and adults.'],
        ],
    ],
    'braces-for-teens' => [
        'title' => 'Braces for Teens', 'description' => 'Braces and clear aligners for teenagers at Ignite Orthodontics. Book a no-cost consultation at one of our Michigan offices.',
        'kicker' => 'Teen Orthodontics', 'h1' => 'Braces That Fit <em>Teen Life.</em>',
        'lead' => 'The teen years are a common time to start orthodontic treatment, once most permanent teeth have come in.',
        'sections' => [
            ['Options teens actually want', 'Choose metal braces in your own colors, tooth-colored ceramic braces, or clear aligners that are removable for meals and special events.'],
            ['Sports and music', 'Teens with braces can keep playing sports and instruments. We recommend a mouthguard during contact sports to protect teeth and lips.'],
            ['What to expect', 'Your consultation includes an exam and a personalized treatment plan with clear timelines and payment options, before you commit to anything.'],
        ],
    ],
    'braces-for-adults' => [
        'title' => 'Braces for Adults', 'description' => 'Adult braces and clear aligners at Ignite Orthodontics. Discreet options, flexible payments and no-cost consultations.',
        'kicker' => 'Adult Orthodontics', 'h1' => 'It Is Never Too Late For <em>a Straighter Smile.</em>',
        'lead' => 'Healthy teeth can be moved at any age. Many of our patients start treatment as adults.',
        'sections' => [
            ['Discreet treatment options', 'Ceramic braces and clear aligners are popular with adults who want treatment to be less noticeable at work and in everyday life.'],
            ['More than appearance', 'Correcting crowding, gaps and bite problems can make teeth easier to clean and help them wear more evenly.'],
            ['Planned around your life', 'We offer convenient appointment times and flexible payment options, and we will explain your insurance benefits before treatment begins.'],
        ],
    ],
    'types-of-braces' => [
        'title' => 'Types of Braces', 'description' => 'Compare traditional metal braces, gold braces and ceramic braces at Ignite Orthodontics.',
        'kicker' => 'Choose Your Style', 'h1' => 'Types of <em>Braces.</em>',
        'lead' => 'Every type of braces we offer is effective. The right choice depends on your goals, your bite and how you want your treatment to look.',
        'links' => [
            ['/types-of-braces/#metal', 'Traditional Metal Braces', 'A proven, cost-effective approach for a wide range of orthodontic concerns.'],
            ['/gold-braces/', 'Gold Braces', 'The reliability of traditional braces with a polished gold finish.'],
            ['/ceramic-braces/', 'Ceramic Braces', 'Tooth-colored brackets for a more subtle appearance.'],
            ['/clear-aligners/', 'Clear Aligners', 'Removable trays that straighten teeth without brackets and wires.'],
        ],
        'sections' => [
            ['Traditional metal braces', 'Metal braces use durable stainless steel brackets and archwires. They are the most cost-effective way to correct alignment and work well for complex cases.'],
        ],
    ],
    'gold-braces' => [
        'title' => 'Gold Braces', 'description' => 'Gold braces at Ignite Orthodontics: the reliability of traditional braces with a polished gold finish.',
        'kicker' => 'Types of Braces', 'h1' => 'Gold <em>Braces.</em>',
        'lead' => 'Gold braces offer the reliability of traditional braces with a polished gold finish, for patients who want their treatment to stand out.',
        'sections' => [
            ['How gold braces work', 'Gold braces work the same way as traditional metal braces: brackets and archwires apply gentle, steady pressure to move teeth into place over time.'],
            ['Is it right for me?', 'Your orthodontist will review your bite and goals at your consultation and explain whether gold braces are a good fit.'],
        ],
    ],
    'ceramic-braces' => [
        'title' => 'Ceramic Braces', 'description' => 'Tooth-colored ceramic braces at Ignite Orthodontics for effective, more subtle orthodontic treatment.',
        'kicker' => 'Types of Braces', 'h1' => 'Ceramic <em>Braces.</em>',
        'lead' => 'Tooth-colored brackets offer effective orthodontic treatment with a more subtle appearance.',
        'sections' => [
            ['Blends in with your smile', 'Ceramic brackets are designed to match the color of your teeth, which makes them less noticeable than metal braces.'],
            ['A popular choice for teens and adults', 'Ceramic braces are often chosen by patients who want the precision of braces with a more discreet look.'],
        ],
    ],
    'clear-aligners' => [
        'title' => 'Clear Aligners', 'description' => 'Clear aligners at Ignite Orthodontics: removable, discreet trays that straighten teeth without brackets and wires.',
        'kicker' => 'Clear Aligner Treatment', 'h1' => 'Clear <em>Aligners.</em>',
        'lead' => 'Removable, discreet trays that gradually straighten your teeth without traditional brackets and wires.',
        'sections' => [
            ['How aligners work', 'You wear a series of custom clear trays, each one moving your teeth a small step closer to their final position.'],
            ['Removable for meals', 'Aligners come out to eat, brush and floss, so there are no food restrictions. They work best when worn as directed, most of the day.'],
            ['Options we offer', 'We offer clear aligner systems including Invisalign® and 3M™ Clear Aligners. Your orthodontist will recommend the best option for your case.'],
        ],
    ],
    'invisalign' => [
        'title' => 'Invisalign', 'description' => 'Invisalign clear aligners for teens and adults at Ignite Orthodontics. Book a no-cost consultation.',
        'kicker' => 'Clear Aligner Treatment', 'h1' => '<em>Invisalign®</em> Clear Aligners.',
        'lead' => 'Invisalign® uses a series of custom clear aligners to straighten teeth discreetly, without brackets or wires.',
        'sections' => [
            ['Virtually invisible', 'The clear trays are hard to notice when you smile or talk, which makes Invisalign® popular with teens and adults.'],
            ['Planned by an orthodontist', 'Your orthodontist designs and monitors your treatment plan and adjusts it as your teeth move.'],
        ],
    ],
    'patient-info' => [
        'title' => 'Patient Info', 'description' => 'What to expect at Ignite Orthodontics: your first visit, insurance, payment options and caring for braces.',
        'kicker' => 'Patient Information', 'h1' => 'What To <em>Expect.</em>',
        'lead' => 'We want every patient and parent to know what happens next, from the first visit to the day braces come off.',
        'sections' => [
            ['Your first visit', 'Your no-cost consultation includes an exam and a conversation about your goals. We will explain your treatment options, timeline and costs before you decide.'],
            ['Insurance and payments', 'We work with many dental insurance plans and can help you understand your benefits. Flexible payment options can spread the remaining cost.'],
            ['During treatment', 'Regular adjustment visits are built into every treatment plan. If something breaks between visits, call your office and we will get you seen.'],
        ],
        'links' => [
            ['/insurance-financing/', 'Insurance & Financing', 'How insurance benefits and payment plans work.'],
            ['/refer-a-patient/', 'Refer a Patient', 'For dentists and families referring someone to us.'],
            ['/contact-us/', 'Contact Us', 'Call or message the office nearest you.'],
        ],
    ],
    'insurance-financing' => [
        'title' => 'Insurance & Financing', 'description' => 'Ignite Orthodontics works with many dental insurance plans and offers flexible payment options.',
        'kicker' => 'Insurance & Payment Options', 'h1' => 'Make the Most of Your <em>Dental Benefits.</em>',
        'lead' => 'Paying for orthodontic treatment should not leave you guessing about what your insurance will cover.',
        'sections' => [
            ['Insurance-friendly care', 'We work with many major insurance plans and will help you understand your orthodontic benefits and expected out-of-pocket costs before treatment begins.'],
            ['Flexible payment options', 'Orthodontic treatment is an investment. Flexible payment options can help you spread the cost of care in a way that works with your budget.'],
        ],
        'note' => 'Insurance coverage varies by plan. Benefits and eligibility should be confirmed before treatment.',
    ],
    'refer-a-patient' => [
        'title' => 'Refer a Patient', 'description' => 'Refer a patient to Ignite Orthodontics. Send us the details and our team will reach out to schedule a consultation.',
        'kicker' => 'Referrals', 'h1' => 'Refer a <em>Patient.</em>',
        'lead' => 'Dentists and families can refer patients to any of our offices. Contact us and our team will follow up to schedule a consultation.',
        'sections' => [
            ['How referrals work', 'Call us or the office nearest the patient with their contact details and any notes about their needs. We will contact the patient directly to arrange a convenient appointment.'],
        ],
    ],
    'contact-us' => [
        'title' => 'Contact Us', 'description' => 'Contact Ignite Orthodontics. Call your nearest office or request a no-cost consultation online.',
        'kicker' => 'Contact Us', 'h1' => 'Your Best Smile Starts With <em>a Conversation.</em>',
        'lead' => 'Request an appointment online or call the office nearest you, and our team will help you schedule your no-cost consultation.',
        'finder' => true,
    ],
    'testimonials' => [
        'title' => 'Patient Reviews', 'description' => 'Read what patients say about Ignite Orthodontics.',
        'kicker' => 'Patients With Reasons to Smile', 'h1' => 'See Why Patients <em>Choose Ignite.</em>',
        'lead' => 'Patient reviews from each of our offices will be published here soon.',
        'draft' => true,
    ],
    'meet-the-doctor' => [
        'title' => 'Meet the Doctors', 'description' => 'Meet the orthodontists at Ignite Orthodontics.',
        'kicker' => 'Your Care Team', 'h1' => 'Meet Your <em>Orthodontists.</em>',
        'lead' => 'Profiles of our orthodontists are coming soon. In the meantime, book a no-cost consultation to meet the team at your nearest office.',
        'draft' => true,
    ],
    'privacy-policy' => [
        'title' => 'Privacy Policy', 'description' => 'Ignite Orthodontics privacy policy.',
        'kicker' => 'Legal', 'h1' => 'Privacy <em>Policy.</em>',
        'lead' => 'Our full privacy policy is being finalized and will be published on this page.',
        'note' => 'If you have questions about how we handle your information, please contact us.',
        'draft' => true,
    ],
    'terms-and-conditions' => [
        'title' => 'Terms and Conditions', 'description' => 'Ignite Orthodontics terms and conditions.',
        'kicker' => 'Legal', 'h1' => 'Terms and <em>Conditions.</em>',
        'lead' => 'Our full terms and conditions, including offer terms and eligibility, are being finalized and will be published on this page.',
        'note' => 'For questions about current offers or terms, please contact us.',
        'draft' => true,
    ],
];
