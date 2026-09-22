<?php
declare(strict_types=1);

// Blocks for the "Treatment Guide" layout (service-guide in app/templates.php).
// A guide page's body is a list of these blocks, which the admin adds, edits
// and reorders; app/views/templates/guide.php draws each one.

/** Icons for cards and payment options (24x24 stroke paths). Keys are what the editor stores. */
function guide_icons(): array
{
    return [
        'check'    => ['Check mark', 'M20 6L9 17l-5-5'],
        'shield'   => ['Shield', 'M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6z M9 12l2 2 4-4'],
        'badge'    => ['Certified', 'M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12z M8.5 14L7 21l5-3 5 3-1.5-7'],
        'star'     => ['Star', 'M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6l-5.4 2.9 1.2-6-4.5-4.2 6.1-.7z'],
        'tooth'    => ['Tooth', 'M7 3C4.5 3 3 5 3 7.5c0 3 1.5 4.5 2 8 .4 3 1 5.5 2.5 5.5 2 0 1.5-5 4.5-5s2.5 5 4.5 5c1.5 0 2.1-2.5 2.5-5.5.5-3.5 2-5 2-8C21 5 19.5 3 17 3c-2 0-3 1-5 1S9 3 7 3z'],
        'smile'    => ['Smile', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z M8.5 14a4 4 0 0 0 7 0 M9 9.5h.01 M15 9.5h.01'],
        'sparkle'  => ['Sparkle', 'M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z M19 3v4 M17 5h4'],
        'eye'      => ['Discreet', 'M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z'],
        'refresh'  => ['Removable', 'M20 11a8 8 0 0 0-14.9-3.9L4 8.5 M4 4v4.5h4.5 M4 13a8 8 0 0 0 14.9 3.9L20 15.5 M20 20v-4.5h-4.5'],
        'layers'   => ['Options', 'M12 3l9 5-9 5-9-5z M3 13l9 5 9-5'],
        'wallet'   => ['Payment', 'M4 7a2 2 0 0 1 2-2h12v4 M4 7v10a2 2 0 0 0 2 2h14V9H6a2 2 0 0 1-2-2z M16 14h.01'],
        'calendar' => ['Calendar', 'M7 3v3 M17 3v3 M4 8h16 M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1z'],
        'clock'    => ['Clock', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z M12 7v5l3 2'],
        'pin'      => ['Location', 'M12 21s-7-6.2-7-12a7 7 0 0 1 14 0c0 5.8-7 12-7 12z M12 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z'],
        'users'    => ['People', 'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z M2 21a7 7 0 0 1 14 0 M16 3.5a4 4 0 0 1 0 7 M18 14a6 6 0 0 1 4 7'],
        'heart'    => ['Heart', 'M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z'],
        'chat'     => ['Conversation', 'M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z'],
        'screen'   => ['Technology', 'M3 5h18v11H3z M8 20h8 M12 16v4'],
        'arrow'    => ['Arrow', 'M5 12h14 M13 6l6 6-6 6'],
        'ball'     => ['Sports', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z M3.5 9.5c3 1 6 1 8.5-1s5.5-3 8.5-2 M3.5 14.5c3-1 6-1 8.5 1s5.5 3 8.5 2'],
        'camera'   => ['Photos', 'M4 8h3l2-3h6l2 3h3v11H4z M12 16.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z'],
        'book'     => ['School', 'M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2z M4 19V5 M8 7h7'],
        'food'     => ['Food', 'M7 3v8 M5 3v5a2 2 0 0 0 4 0V3 M7 11v10 M17 3c-2 1.5-3 4-3 7h3v11'],
        'brush'    => ['Cleaning', 'M6 21l5-5 M14 3l7 7-6 6-7-7z M11 9l4 4'],
        'palette'  => ['Colors', 'M12 3a9 9 0 1 0 0 18c1.1 0 2-.9 2-2 0-.5-.2-1-.5-1.3-.3-.4-.5-.8-.5-1.3 0-1.1.9-2 2-2h2.4A4.6 4.6 0 0 0 21 9.8C21 6 17 3 12 3z M7.5 11h.01 M10 7h.01 M15 7h.01'],
        'crowding' => ['Crowded teeth', 'M5 7v10 M9.5 6v12 M14.5 6v12 M19 7v10'],
        'gap'      => ['Gaps', 'M6 6v12 M18 6v12 M9 12h6'],
        'bite'     => ['Bite', 'M4 9h16 M6 9v3a6 6 0 0 0 12 0V9 M8 15h8'],
        'cross'    => ['Crossbite', 'M5 8l14 8 M5 16l14-8'],
    ];
}

/** The blocks a "Treatment Guide" page is built from, with the fields each one offers. */
function guide_block_types(): array
{
    static $types;
    if ($types !== null) {
        return $types;
    }

    $icons = array_map(static fn(array $icon): string => $icon[0], guide_icons());
    $tones = ['white' => 'White', 'tint' => 'Light grey', 'navy' => 'Navy'];
    // the first option is what a new block starts with
    $tone = static fn(string $first): array => [
        'key' => 'tone', 'type' => 'select', 'label' => 'Background',
        'options' => [$first => $tones[$first]] + $tones,
    ];
    $head = [
        ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Small label'],
        ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading'],
        ['key' => 'intro', 'type' => 'textarea', 'label' => 'Intro text', 'help' => 'Each line becomes its own paragraph.'],
    ];
    $button = ['key' => 'button', 'type' => 'text', 'label' => 'Booking button (optional)', 'help' => 'Leave empty for no button. The button opens the booking popup.'];
    $note   = ['key' => 'note', 'type' => 'textarea', 'label' => 'Text under the block (optional)', 'help' => 'Each line becomes its own paragraph.'];

    return $types = [
        'text' => [
            'label' => 'Text and checklist',
            'help'  => 'Paragraphs with an optional list and photo.',
            'fields' => array_merge($head, [
                ['key' => 'list', 'type' => 'lines', 'label' => 'List (optional)', 'help' => 'One item per line.'],
                ['key' => 'list_style', 'type' => 'select', 'label' => 'List style', 'options' => ['check' => 'Check marks', 'dot' => 'Bullets', 'quote' => 'Speech bubbles']],
                ['key' => 'outro', 'type' => 'textarea', 'label' => 'Text after the list (optional)', 'help' => 'Each line becomes its own paragraph.'],
                ['key' => 'image', 'type' => 'image', 'label' => 'Photo (optional)'],
                ['key' => 'image_side', 'type' => 'select', 'label' => 'Photo side', 'options' => ['right' => 'Right', 'left' => 'Left']],
                $button,
                $tone('white'),
            ]),
        ],
        'cards' => [
            'label' => 'Feature cards',
            'help'  => 'A grid of cards with an icon, title and text.',
            'fields' => array_merge($head, [
                ['key' => 'items', 'type' => 'list', 'label' => 'Cards', 'item' => 'Card', 'max' => 12, 'fields' => [
                    ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => $icons],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                ]],
                $note,
                $button,
                $tone('tint'),
            ]),
        ],
        'steps' => [
            'label' => 'Numbered steps',
            'help'  => 'A process, numbered in order.',
            'fields' => array_merge($head, [
                ['key' => 'items', 'type' => 'list', 'label' => 'Steps', 'item' => 'Step', 'max' => 10, 'fields' => [
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                ]],
                $note,
                $button,
                $tone('white'),
            ]),
        ],
        'panels' => [
            'label' => 'Side-by-side panels',
            'help'  => 'Larger panels, for example Teens and Adults, or the types of braces.',
            'fields' => array_merge($head, [
                ['key' => 'items', 'type' => 'list', 'label' => 'Panels', 'item' => 'Panel', 'max' => 6, 'fields' => [
                    ['key' => 'label', 'type' => 'text', 'label' => 'Small label'],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text', 'help' => 'Each line becomes its own paragraph.'],
                    ['key' => 'link', 'type' => 'link', 'label' => 'Links to (optional)'],
                    ['key' => 'link_label', 'type' => 'text', 'label' => 'Link text'],
                ]],
                $note,
                $button,
                $tone('white'),
            ]),
        ],
        'compare' => [
            'label' => 'Comparison table',
            'help'  => 'Compare treatments side by side.',
            'fields' => array_merge($head, [
                ['key' => 'columns', 'type' => 'lines', 'label' => 'Column headings', 'help' => 'One per line, for example Invisalign then Traditional Braces.'],
                ['key' => 'rows', 'type' => 'list', 'label' => 'Rows', 'item' => 'Row', 'max' => 20, 'fields' => [
                    ['key' => 'label', 'type' => 'text', 'label' => 'Row label (optional)'],
                    ['key' => 'values', 'type' => 'lines', 'label' => 'Values', 'help' => 'One per line, in the same order as the column headings.'],
                ]],
                $note,
                $button,
                $tone('tint'),
            ]),
        ],
        'pricing' => [
            'label' => 'Cost and payment',
            'help'  => 'The price headline and the ways to pay.',
            'fields' => array_merge($head, [
                ['key' => 'items', 'type' => 'list', 'label' => 'Payment options', 'item' => 'Option', 'max' => 6, 'fields' => [
                    ['key' => 'icon', 'type' => 'select', 'label' => 'Icon', 'options' => $icons],
                    ['key' => 'title', 'type' => 'text', 'label' => 'Title'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text'],
                ]],
                $note,
                $button,
                $tone('navy'),
            ]),
        ],
        'doctor' => [
            'label' => 'Meet the doctor',
            'fields' => [
                ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Small label'],
                ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading'],
                ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                ['key' => 'credential', 'type' => 'text', 'label' => 'Title or credential'],
                ['key' => 'intro', 'type' => 'textarea', 'label' => 'Text', 'help' => 'Each line becomes its own paragraph.'],
                ['key' => 'image', 'type' => 'image', 'label' => 'Photo (optional)'],
                $tone('white'),
            ],
        ],
        'reviews' => [
            'label' => 'Patient reviews',
            'help'  => 'Only publish genuine reviews from Ignite patients.',
            'fields' => array_merge($head, [
                ['key' => 'items', 'type' => 'list', 'label' => 'Reviews', 'item' => 'Review', 'max' => 12, 'fields' => [
                    ['key' => 'quote', 'type' => 'textarea', 'label' => 'Review'],
                    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                ]],
                $tone('tint'),
            ]),
        ],
        'cta' => [
            'label' => 'Call-to-action band',
            'help'  => 'A bold band with a booking button.',
            'fields' => [
                ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Small label'],
                ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading'],
                ['key' => 'intro', 'type' => 'textarea', 'label' => 'Text', 'help' => 'Each line becomes its own paragraph.'],
                ['key' => 'price', 'type' => 'text', 'label' => 'Price line (optional)'],
                ['key' => 'button', 'type' => 'text', 'label' => 'Button', 'default' => 'Schedule Your Free Consultation'],
                ['key' => 'call', 'type' => 'select', 'label' => 'Show a call button', 'options' => ['yes' => 'Yes', 'no' => 'No']],
                ['key' => 'tone', 'type' => 'select', 'label' => 'Background', 'options' => ['orange' => 'Orange', 'navy' => 'Navy']],
            ],
        ],
        'stats' => [
            'label' => 'Highlights strip',
            'help'  => 'Three or four short highlights in a row. Only use numbers the practice can stand behind.',
            'fields' => [
                ['key' => 'items', 'type' => 'list', 'label' => 'Highlights', 'item' => 'Highlight', 'max' => 4, 'fields' => [
                    ['key' => 'value', 'type' => 'text', 'label' => 'Big text', 'help' => 'For example: Free, $99, Board'],
                    ['key' => 'label', 'type' => 'text', 'label' => 'Label'],
                    ['key' => 'text', 'type' => 'textarea', 'label' => 'Text (optional)'],
                ]],
                $tone('white'),
            ],
        ],
        'beforeafter' => [
            'label' => 'Before and after',
            'help'  => 'Two photos side by side. Only use real photos of Ignite patients, with their permission.',
            'fields' => [
                ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Small label'],
                ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading'],
                ['key' => 'before', 'type' => 'image', 'label' => 'Before photo'],
                ['key' => 'after', 'type' => 'image', 'label' => 'After photo'],
                ['key' => 'note', 'type' => 'text', 'label' => 'Caption', 'default' => 'Real patient. Individual results may vary.'],
                $button,
                $tone('tint'),
            ],
        ],
        'faq' => [
            'label' => 'Questions and answers',
            'help'  => 'Shown as an accordion, and offered to Google as FAQ results.',
            'fields' => [
                ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Small label'],
                ['key' => 'heading', 'type' => 'text', 'em' => true, 'label' => 'Heading'],
                ['key' => 'items', 'type' => 'list', 'label' => 'Questions', 'item' => 'Question', 'max' => 30, 'fields' => [
                    ['key' => 'q', 'type' => 'text', 'label' => 'Question'],
                    ['key' => 'a', 'type' => 'textarea', 'label' => 'Answer', 'help' => 'Each line becomes its own paragraph.'],
                ]],
                $tone('white'),
            ],
        ],
    ];
}

/** Plain text with one paragraph per line (the "Each line becomes its own paragraph" fields). */
function guide_paragraphs(string $text, string $class = ''): string
{
    $out = '';
    foreach (preg_split('/\R/', $text) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '') {
            $out .= '<p' . ($class !== '' ? ' class="' . $class . '"' : '') . '>' . tpl_em($line) . '</p>';
        }
    }
    return $out;
}

function guide_icon(string $key): string
{
    $icons = guide_icons();
    $path  = ($icons[$key] ?? $icons['check'])[1];
    return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="' . e($path) . '"/></svg>';
}
