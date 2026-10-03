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
    // Describes exactly what the website does: the booking, video consultation and
    // See Your Smile forms, what is kept when a form is started but not sent, the
    // photo handling, and the third parties the pages load (Google, Meta).
    // Anything changed in those features belongs here too.
    'privacy-policy' => [
        'title' => 'Privacy Policy', 'description' => 'How Ignite Orthodontics collects, uses and protects the information you share through this website.',
        'kicker' => 'Legal', 'h1' => 'Privacy <em>Policy.</em>',
        'lead' => 'This policy explains what we collect through igniteorthodontics.com, why we collect it, who sees it and how to ask us to change or delete it.',
        'note' => 'Last updated 3 October 2026. This policy covers this website. The health records we keep as your orthodontic practice are covered separately by our Notice of Privacy Practices, which we give you at your first visit.',
        'sections' => [
            ['Who this policy is from',
                'Ignite Orthodontics ("we", "us") operates this website and the orthodontic offices listed on it in Michigan. If you have a question about anything here, call us on (947) 254-1718 or email booking@igniteorthodontics.com.'],

            ['What you give us', [
                'We only ask for what we need in order to answer you. Depending on which form you use, that is:',
                [
                    'Your name, phone number and email address',
                    'The office you chose, and for an appointment request the day and time that suit you',
                    'Who the treatment is for, and which treatment you are interested in',
                    'Anything you write in the notes box',
                    'For a video consultation, the time you booked, which is added to our appointment calendar',
                    'For See Your Smile, the photo you send and what you told us bothers you about your smile',
                ],
                'You do not need an account, and we never ask for payment details on this website.',
            ]],

            ['If you start a form and do not finish it', [
                'If you type your email address or phone number into one of our forms and then leave without sending it, we keep what you typed so that our team can follow up and help.',
                'We treat those details differently from a request you actually sent. They are marked in our system as unfinished, and they are not treated as permission to market to you. If you would rather we did not keep them, email booking@igniteorthodontics.com and we will delete them.',
            ]],

            ['The photos you send us', [
                'A photo of your smile is personal, so we handle it carefully:',
                [
                    'It is stored privately on our server, outside the public website, and cannot be reached by anyone browsing the internet',
                    'Only signed-in members of our team can open it',
                    'It is deleted automatically after 90 days',
                    'We never publish it, post it on social media or use it in advertising unless you give us separate written permission',
                ],
                'If we show you a preview of a straighter smile, that image is produced by automated image software and is an illustration only. It is not a diagnosis, a treatment plan or a promise of any result. To produce it, your photo is sent to our image processing provider, which processes it on our behalf and does not use it to train its systems. What is actually achievable for you can only be determined by an orthodontist examining your teeth.',
            ]],

            ['What we collect automatically', [
                'When you send a form we also record the internet address (IP address) and browser your device used, which helps us investigate abuse of the forms and stop automated spam. We keep a one-way scrambled copy of IP addresses to limit how often the same device can submit a form.',
                'Our pages use the following services, which may set cookies or collect information about your visit:',
                [
                    'Google Tag Manager and Google Analytics, so we can see which pages people find useful',
                    'The Meta (Facebook) pixel, which tells us whether our advertising is working and lets us show ads to people who have visited the site',
                    'Google Maps, embedded on our office pages so you can see where we are',
                    'Google Calendar and Google Meet, used to arrange and host video consultations',
                ],
                'You can refuse or delete cookies in your browser settings, and you can opt out of personalised advertising through your Google and Meta account settings. Refusing them does not stop you from booking an appointment.',
            ]],

            ['How we use what you share', [
                'We use your information to:',
                [
                    'Reply to you and arrange your consultation',
                    'Prepare for your visit and provide orthodontic care',
                    'Send you an appointment confirmation and the link to a video consultation',
                    'Answer questions about treatment, insurance and payment options',
                    'Improve our website and understand which of our advertising is worth running',
                ],
                'We do not sell your information, and we do not rent or trade it.',
            ]],

            ['Calls, texts and emails', [
                'When you tick the consent box on a form, you are agreeing that we may call or text you about your care and about our services, including by automated systems, at the number you gave us. You do not have to agree to this in order to become a patient or to buy anything from us.',
                'You can stop marketing messages at any time: reply STOP to a text, use the unsubscribe link in an email, or tell any member of our team. We will still contact you about an appointment you have booked. Message and data rates may apply depending on your phone plan.',
            ]],

            ['Who else sees your information', [
                'We share what you send only with people who need it to do their job:',
                [
                    'The Ignite Orthodontics office you chose, and our central booking team',
                    'The companies that run our website, email and appointment systems on our behalf',
                    'Our professional advisers, and anyone we are required by law to tell',
                ],
                'Those companies may only use your information to provide their service to us.',
            ]],

            ['How long we keep it', [
                'Smile photos are deleted after 90 days. Appointment requests and the messages you send us are kept for as long as we need them to care for you and to meet our legal and accounting obligations, and are then deleted. Your clinical records, once you are a patient, are kept for the period Michigan law requires.',
            ]],

            ['Your choices', [
                'You can ask us to:',
                [
                    'Tell you what information we hold about you',
                    'Correct anything that is wrong',
                    'Delete the information you sent through this website, including any photo',
                    'Stop contacting you for marketing',
                ],
                'Email booking@igniteorthodontics.com or call (947) 254-1718 and we will deal with it. Deleting information we need for your clinical record may not be possible, and we will explain if that applies.',
            ]],

            ['Children', [
                'We treat children, and we expect a parent or guardian to complete our forms on behalf of anyone under 18. We do not knowingly collect information directly from a child under 13. If you believe a child has sent us information without a parent\'s involvement, contact us and we will delete it.',
            ]],

            ['Keeping it safe', [
                'Our website is served over an encrypted connection, photos and booking details are stored outside the public part of the website, and access to them requires a password. No system is perfect, but we take care with what you trust us with, and we limit who in the practice can see it.',
            ]],

            ['Changes to this policy', [
                'If we change how we handle your information, we will update this page and change the date at the top. Material changes will be made clear on the page.',
            ]],
        ],
    ],
    'terms-and-conditions' => [
        'title' => 'Terms and Conditions', 'description' => 'Ignite Orthodontics terms and conditions.',
        'kicker' => 'Legal', 'h1' => 'Terms and <em>Conditions.</em>',
        'lead' => 'Our full terms and conditions, including offer terms and eligibility, are being finalized and will be published on this page.',
        'note' => 'For questions about current offers or terms, please contact us.',
        'draft' => true,
    ],
];
