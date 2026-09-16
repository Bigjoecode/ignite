<?php
// Starter content for the remaining static pages (about, patient info, legal).
// Treatment pages and office pages are no longer here: they live in the CMS and
// are edited in /admin/pages/. 'draft' pages are live but noindex and left out
// of the sitemap until real content replaces the starter copy.
//
// Fields: title, description, kicker, h1 (may contain <em>), lead,
//         sections [[heading, paragraph], ...], links [[href, label, text], ...],
//         links_auto (list the treatment pages from the CMS), note (highlighted
//         notice), finder (show office list), draft
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
        'links_auto' => true, // every treatment page that is set to show in the menu
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
