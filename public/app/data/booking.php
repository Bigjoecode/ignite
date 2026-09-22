<?php
// Choices offered on the booking page (views/booking.php).
// app/book.php validates submissions against these same keys.
// Icons are 24x24 stroke paths.
return [
    'patients' => [
        'self'  => ['Myself', 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M4 21a8 8 0 0 1 16 0'],
        'child' => ['My child', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z M8.5 14a4 4 0 0 0 7 0 M9 9.5h.01 M15 9.5h.01'],
        'teen'  => ['My teen', 'M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6l-5.4 2.9 1.2-6-4.5-4.2 6.1-.7z'],
    ],
    'treatments' => [
        'braces'   => ['Metal braces', 'M3 12h18 M6 9h3v6H6z M15 9h3v6h-3z'],
        'aligners' => ['Clear aligners / Invisalign®', 'M4 9c0 6 3 9 8 9s8-3 8-9 M7 9c0 4 2 6 5 6s5-2 5-6'],
        'ceramic'  => ['Ceramic braces', 'M7 3C4.5 3 3 5 3 7.5c0 3 1.5 4.5 2 8 .4 3 1 5.5 2.5 5.5 2 0 1.5-5 4.5-5s2.5 5 4.5 5c1.5 0 2.1-2.5 2.5-5.5.5-3.5 2-5 2-8C21 5 19.5 3 17 3c-2 0-3 1-5 1S9 3 7 3z'],
        'gold'     => ['Gold braces', 'M12 3v4 M12 17v4 M3 12h4 M17 12h4 M6 6l2.5 2.5 M15.5 15.5L18 18 M6 18l2.5-2.5 M15.5 8.5L18 6'],
        'unsure'   => ['Not sure yet', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6 M12 17h.01'],
    ],
    'times' => [
        'any'       => 'Any time',
        'morning'   => 'Morning',
        'afternoon' => 'Afternoon',
        'evening'   => 'Evening',
    ],
    'days_ahead' => 14, // preferred-day choices start tomorrow
];
