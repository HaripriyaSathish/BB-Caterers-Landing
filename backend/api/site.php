<?php
/**
 * GET /api/site.php
 * Returns every piece of landing-page content in one response.
 */
declare(strict_types=1);

require __DIR__ . '/_init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Settings — never expose SMTP config; turn stored image paths into absolute URLs.
$imageKeys = ['logo', 'favicon', 'apple_touch_icon', 'about_image_1', 'about_image_2', 'highlight_image',
              'theme_page_bg', 'theme_divider', 'theme_flourish', 'theme_arch', 'og_image'];
$settings = [];
foreach (settings_all() as $key => $value) {
    if (str_starts_with($key, 'smtp_')) {
        continue;
    }
    if (in_array($key, $imageKeys, true)) {
        $value = media_url($value);
    } elseif (str_ends_with($key, '_enabled')) {
        $value = $value === '1';
    }
    $settings[$key] = $value;
}
$settings['footer_copyright'] = fill_template((string) ($settings['footer_copyright'] ?? ''), ['year' => date('Y')]);

$active = fn(string $table, string $cols) =>
    DB::all("SELECT $cols FROM `$table` WHERE is_active = 1 ORDER BY sort_order, id");

$withImage = function (array $rows, string $field = 'image'): array {
    foreach ($rows as &$r) {
        $r[$field] = media_url($r[$field] ?? null);
    }
    return $rows;
};

$castInt = function (array $rows, array $fields): array {
    foreach ($rows as &$r) {
        foreach ($fields as $f) {
            $r[$f] = (int) $r[$f];
        }
    }
    return $rows;
};

$events = $withImage($castInt($active('events', 'id, title, image, icon'), ['id']));

// Always fresh so admin changes appear immediately (the response is small)
header('Cache-Control: no-cache, must-revalidate');

json_response([
    'success' => true,
    'data' => [
        'settings'     => $settings,
        'menu'         => $castInt($active('menu_items', 'id, label, url'), ['id']),
        'hero'         => $withImage($castInt($active('hero_slides', 'id, eyebrow, title, subtitle, image, btn1_text, btn1_link, btn2_text, btn2_link'), ['id'])),
        'services'     => $withImage($castInt($active('services', 'id, title, description, image, icon'), ['id'])),
        'gallery'      => $withImage($castInt($active('gallery', 'id, title, image, layout'), ['id'])),
        'highlights'   => $castInt($active('highlights', 'id, title, icon'), ['id']),
        'events'       => $events,
        'features'     => $castInt($active('features', 'id, title, description, icon'), ['id']),
        'testimonials' => $withImage($castInt($active('testimonials', 'id, name, role, message, rating, photo'), ['id', 'rating']), 'photo'),
        // Options for the enquiry form "Event type" select
        'event_types'  => array_merge(array_column($events, 'title'), ['Other']),
        // Same limits the server enforces — the React form mirrors these
        'form_rules'   => (function () {
            require_once __DIR__ . '/../core/validation.php';
            $r = enquiry_rules();
            $r['event_date']['min'] = date('Y-m-d', strtotime('+' . $r['event_date']['min_days_ahead'] . ' day'));
            $r['event_date']['max'] = date('Y-m-d', strtotime('+' . $r['event_date']['max_days_ahead'] . ' day'));
            unset($r['event_type']['options']);
            return $r;
        })(),
    ],
]);
