<?php
declare(strict_types=1);

/**
 * Schema.org data for the practice and for each office.
 *
 * The practice is one Organization (#practice). Each office is a Dentist — a kind of
 * LocalBusiness — with its own @id, address, phone and map link, listed as a department
 * of the practice. Google reads those to show an office in local results and on Maps.
 *
 * Everything here comes from the office pages in /admin/pages/?type=location, so a
 * detail corrected there is corrected in the markup on the next page view. Nothing is
 * invented: a field the practice has not filled in is simply left out.
 */

const SCHEMA_DAYS = [
    'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday',
    'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday',
];

/** The practice itself, which every other node points at. */
function schema_practice(): array
{
    $base = cfg('base_url');
    $org  = [
        '@type'       => 'Organization',
        '@id'         => $base . '/#practice',
        'name'        => cfg('site_name'),
        'url'         => $base . '/',
        'logo'        => ['@type' => 'ImageObject', 'url' => $base . '/assets/img/ignitelogo.png'],
        'description' => 'Braces and clear aligners for children, teens and adults at '
            . count(locations()) . ' orthodontic offices in Michigan.',
    ];
    if ($social = array_values(array_filter((array) cfg('social')))) {
        $org['sameAs'] = $social;
    }
    if (($doctor = schema_doctor()) !== []) {
        $org['employee'] = [['@id' => $doctor['@id']]];
    }
    return $org;
}

/** The orthodontist, or [] when the practice has not named one. */
function schema_doctor(): array
{
    $name = trim((string) cfg('doctor_name'));
    if ($name === '') {
        return [];
    }
    $base   = cfg('base_url');
    $doctor = [
        '@type'       => 'Person',
        '@id'         => $base . '/meet-the-doctor/#doctor',
        'name'        => $name,
        'url'         => $base . '/meet-the-doctor/',
        'jobTitle'    => 'Orthodontist',
        'worksFor'    => ['@id' => $base . '/#practice'],
        'knowsAbout'  => ['Orthodontics', 'Braces', 'Clear aligners', 'Invisalign'],
    ];
    if (($credential = trim((string) cfg('doctor_credential'))) !== '') {
        $doctor['honorificSuffix'] = $credential;
    }
    return $doctor;
}

/** One office as a local business: address, phone, map, the days it sees patients. */
function schema_office(array $office, string $image = ''): array
{
    $base = cfg('base_url');
    $url  = $base . '/locations/' . $office['slug'] . '/';
    $node = [
        '@type'              => ['Dentist', 'MedicalBusiness'],
        '@id'                => $url . '#office',
        'name'               => cfg('site_name') . ' ' . $office['name'],
        'url'                => $url,
        'parentOrganization' => ['@id' => $base . '/#practice'],
        'medicalSpecialty'   => 'Orthodontic',
        'priceRange'         => '$$',
        'currenciesAccepted' => 'USD',
        'address' => array_filter([
            '@type'           => 'PostalAddress',
            'streetAddress'   => $office['street'],
            'addressLocality' => $office['city'],
            'addressRegion'   => $office['state'],
            'postalCode'      => $office['zip'],
            'addressCountry'  => 'US',
        ]),
    ];
    if ($office['tel'] !== '')   $node['telephone'] = $office['tel'];
    if ($office['email'] !== '') $node['email'] = $office['email'];
    if ($image !== '')           $node['image'] = $base . $image;
    if ($office['city'] !== '')  $node['areaServed'] = ['@type' => 'City', 'name' => $office['city'] . ', MI'];
    if ($office['map_q'] !== '') {
        $node['hasMap'] = map_url($office['map_q']);
    }
    if (($hours = schema_hours($office)) !== []) {
        $node['openingHoursSpecification'] = $hours;
    }
    return $node;
}

/**
 * Opening hours from the office page. "Mon-Fri: 9AM - 5PM" becomes a full
 * specification; when only the appointment days are known, the days are declared
 * without times, which is all the practice has actually told us.
 */
function schema_hours(array $office): array
{
    $spec = [];
    foreach ($office['hours'] as $line) {
        if (!preg_match('/^\s*([A-Za-z]{3})[A-Za-z]*\s*(?:[-–]\s*([A-Za-z]{3})[A-Za-z]*)?\s*:?\s*(\d{1,2})(?::(\d{2}))?\s*([AaPp])\.?[Mm]\.?\s*[-–to]+\s*(\d{1,2})(?::(\d{2}))?\s*([AaPp])\.?[Mm]\.?/', $line, $m)) {
            continue;
        }
        $days  = array_keys(SCHEMA_DAYS);
        $first = array_search(strtolower($m[1]), $days, true);
        $last  = $m[2] !== '' ? array_search(strtolower($m[2]), $days, true) : $first;
        if ($first === false || $last === false) {
            continue;
        }
        $named = [];
        for ($i = $first; $i <= $last; $i++) {
            $named[] = SCHEMA_DAYS[$days[$i]];
        }
        $spec[] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => $named,
            'opens'     => schema_time($m[3], $m[4], $m[5]),
            'closes'    => schema_time($m[6], $m[7], $m[8]),
        ];
    }
    if ($spec === [] && $office['days'] !== []) {
        $spec[] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => array_map(static fn(string $d): string => SCHEMA_DAYS[$d], $office['days']),
        ];
    }
    return $spec;
}

/** "9", "30", "p" becomes "21:30". */
function schema_time(string $hour, string $minute, string $half): string
{
    $h = (int) $hour % 12;
    if (strtolower($half) === 'p') {
        $h += 12;
    }
    return sprintf('%02d:%02d', $h, $minute === '' ? 0 : (int) $minute);
}

/** Practice plus every office, for the home page and the locations index. */
function schema_all_offices(): array
{
    $graph = [schema_practice()];
    if (($doctor = schema_doctor()) !== []) {
        $graph[] = $doctor;
    }
    $offices = [];
    foreach (locations() as $office) {
        $node      = schema_office($office);
        $graph[]   = $node;
        $offices[] = ['@id' => $node['@id']];
    }
    $graph[0]['department'] = $offices;
    return ['@context' => 'https://schema.org', '@graph' => $graph];
}
