<?php
declare(strict_types=1);

/**
 * Everything the admin can edit is described here, so the screens are generated
 * consistently. Field types: text, textarea, url, email, number, image, icon, select, toggle, rating.
 */

/** Icons offered in the icon picker (Lucide names — the React site uses the same set). */
const ICON_CHOICES = [
    'chef-hat', 'cooking-pot', 'soup', 'salad', 'utensils', 'utensils-crossed', 'hand-platter', 'concierge-bell',
    'flame', 'leaf', 'wheat', 'cake-slice', 'cookie', 'coffee', 'ice-cream-cone', 'cherry', 'apple',
    'gift', 'party-popper', 'sparkles', 'star', 'heart', 'heart-handshake', 'handshake', 'gem', 'award', 'medal',
    'crown', 'badge-check', 'shield-check', 'thumbs-up', 'smile', 'users', 'user-round', 'briefcase', 'building-2',
    'calendar-heart', 'calendar-days', 'clock', 'truck', 'map-pin', 'phone', 'mail', 'music', 'camera',
    'flower-2', 'sun', 'house', 'landmark', 'church',
    // custom icons drawn to match the Figma design
    'award-star', 'rings', 'laddoo',
];

/** List-type sections (multiple rows, drag to reorder, show/hide). */
function list_modules(): array
{
    return [
        'hero' => [
            'table' => 'hero_slides', 'title' => 'Hero Slider', 'singular' => 'Slide', 'icon' => 'gallery-horizontal-end',
            'folder' => 'hero', 'intro' => 'The large rotating banner at the top of the website.',
            'columns' => ['image' => 'Image', 'title' => 'Heading', 'eyebrow' => 'Small label'],
            'settings' => [
                'hero_autoplay_seconds' => ['type' => 'number', 'label' => 'Auto-slide every (seconds)', 'help' => 'Set 0 to turn off auto-play.', 'min' => 0, 'max' => 30],
            ],
            'fields' => [
                'eyebrow'  => ['type' => 'text', 'label' => 'Small label above heading', 'placeholder' => 'Traditional Indian Feast'],
                'title'    => ['type' => 'textarea', 'label' => 'Heading', 'required' => true, 'rows' => 2],
                'subtitle' => ['type' => 'textarea', 'label' => 'Description', 'rows' => 3],
                'image'    => ['type' => 'image', 'label' => 'Background image', 'required' => true, 'help' => 'Wide image, at least 1440 × 630 px.'],
                'btn1_text' => ['type' => 'text', 'label' => 'Primary button text', 'half' => true],
                'btn1_link' => ['type' => 'text', 'label' => 'Primary button link', 'half' => true, 'placeholder' => '#services'],
                'btn2_text' => ['type' => 'text', 'label' => 'Second button text', 'half' => true],
                'btn2_link' => ['type' => 'text', 'label' => 'Second button link', 'half' => true, 'placeholder' => '#contact'],
            ],
        ],
        'services' => [
            'table' => 'services', 'title' => 'Services / Specialities', 'singular' => 'Service', 'icon' => 'chef-hat',
            'folder' => 'services', 'intro' => 'The "Our Specialities" cards. These also appear in the footer and enquiry emails.',
            'columns' => ['image' => 'Image', 'title' => 'Title', 'description' => 'Description', 'icon' => 'Icon'],
            'settings' => [
                'services_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'services_eyebrow' => ['type' => 'text', 'label' => 'Small label', 'half' => true],
                'services_title'   => ['type' => 'text', 'label' => 'Section heading', 'half' => true],
            ],
            'fields' => [
                'title'       => ['type' => 'text', 'label' => 'Title', 'required' => true],
                'description' => ['type' => 'text', 'label' => 'Short description'],
                'icon'        => ['type' => 'icon', 'label' => 'Icon'],
                'image'       => ['type' => 'image', 'label' => 'Image', 'required' => true, 'help' => 'Landscape image, about 800 × 450 px.'],
            ],
        ],
        'gallery' => [
            'table' => 'gallery', 'title' => 'Food Gallery', 'singular' => 'Photo', 'icon' => 'images',
            'folder' => 'gallery', 'intro' => 'Photos in the "A Feast for the Eyes" grid. Order decides the position (see help on each photo).',
            'columns' => ['image' => 'Photo', 'title' => 'Caption (hover text)'],
            'settings' => [
                'gallery_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'gallery_eyebrow' => ['type' => 'text', 'label' => 'Small label', 'half' => true],
                'gallery_title'   => ['type' => 'text', 'label' => 'Section heading', 'half' => true],
            ],
            'fields' => [
                'title'  => ['type' => 'text', 'label' => 'Caption (shown on hover)', 'placeholder' => 'Masala dosa with chutneys'],
                'image'  => ['type' => 'image', 'label' => 'Photo', 'required' => true,
                             'help' => 'The grid follows the Figma layout in groups of 7: 1 = large, 2 = tall, 3–4 = stacked, 5 = wide, 6–7 = normal. Drag rows to choose positions.'],
            ],
        ],
        'highlights' => [
            'table' => 'highlights', 'title' => 'Highlight Banner', 'singular' => 'Point', 'icon' => 'sparkles',
            'folder' => 'highlight', 'intro' => 'The "Art of Authentic Indian Catering" banner and its bullet points.',
            'columns' => ['icon' => 'Icon', 'title' => 'Text'],
            'settings' => [
                'highlight_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'highlight_title'   => ['type' => 'text', 'label' => 'Banner heading'],
                'highlight_image'   => ['type' => 'image', 'label' => 'Banner background', 'help' => 'Very wide image, about 2000 × 480 px.'],
            ],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Text', 'required' => true],
                'icon'  => ['type' => 'icon', 'label' => 'Icon'],
            ],
        ],
        'events' => [
            'table' => 'events', 'title' => 'Events We Cater', 'singular' => 'Event', 'icon' => 'party-popper',
            'folder' => 'events', 'intro' => 'The "Made for Moments Worth Celebrating" cards. Titles also fill the enquiry form\'s Event Type list.',
            'columns' => ['image' => 'Image', 'title' => 'Title', 'icon' => 'Icon'],
            'settings' => [
                'events_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'events_title'   => ['type' => 'text', 'label' => 'Section heading', 'help' => 'Wrap words in *asterisks* to show them in gold, e.g. Made for *Moments* Worth *Celebrating*'],
            ],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title', 'required' => true],
                'icon'  => ['type' => 'icon', 'label' => 'Icon'],
                'image' => ['type' => 'image', 'label' => 'Image', 'required' => true],
            ],
        ],
        'features' => [
            'table' => 'features', 'title' => 'Why Choose Us', 'singular' => 'Reason', 'icon' => 'award',
            'folder' => 'features', 'intro' => 'The "Excellence in Every Bite" cards.',
            'columns' => ['icon' => 'Icon', 'title' => 'Title', 'description' => 'Description'],
            'settings' => [
                'features_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'features_eyebrow' => ['type' => 'text', 'label' => 'Small label', 'half' => true],
                'features_title'   => ['type' => 'text', 'label' => 'Section heading', 'half' => true],
            ],
            'fields' => [
                'title'       => ['type' => 'text', 'label' => 'Title', 'required' => true],
                'description' => ['type' => 'text', 'label' => 'Short description'],
                'icon'        => ['type' => 'icon', 'label' => 'Icon'],
            ],
        ],
        'testimonials' => [
            'table' => 'testimonials', 'title' => 'Testimonials', 'singular' => 'Testimonial', 'icon' => 'message-square-quote',
            'folder' => 'testimonials', 'intro' => 'Customer reviews shown in the testimonial slider.',
            'columns' => ['name' => 'Customer', 'message' => 'Review', 'rating' => 'Rating'],
            'settings' => [
                'testimonials_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'testimonials_eyebrow' => ['type' => 'text', 'label' => 'Small label', 'half' => true],
                'testimonials_title'   => ['type' => 'text', 'label' => 'Section heading', 'half' => true],
            ],
            'fields' => [
                'name'    => ['type' => 'text', 'label' => 'Customer name', 'required' => true, 'half' => true],
                'role'    => ['type' => 'text', 'label' => 'Event / place', 'half' => true, 'placeholder' => 'Wedding Reception'],
                'message' => ['type' => 'textarea', 'label' => 'Review', 'required' => true, 'rows' => 4],
                'rating'  => ['type' => 'rating', 'label' => 'Rating'],
                'photo'   => ['type' => 'image', 'label' => 'Photo (optional)'],
            ],
        ],
        'menu' => [
            'table' => 'menu_items', 'title' => 'Header Menu', 'singular' => 'Menu link', 'icon' => 'menu',
            'folder' => 'misc', 'intro' => 'Links in the top navigation and the footer "Explore" column.',
            'columns' => ['label' => 'Label', 'url' => 'Link'],
            'settings' => [
                'header_cta_text' => ['type' => 'text', 'label' => 'Header button text', 'half' => true],
                'header_cta_link' => ['type' => 'text', 'label' => 'Header button link', 'half' => true],
            ],
            'fields' => [
                'label' => ['type' => 'text', 'label' => 'Label', 'required' => true],
                'url'   => ['type' => 'text', 'label' => 'Link', 'required' => true, 'help' => 'Use #about, #services, #gallery, #contact to jump to a section, or a full https:// address.'],
            ],
        ],
    ];
}

/** Settings-only pages (single set of values). */
function settings_groups(): array
{
    return [
        'branding' => [
            'title' => 'Branding & SEO', 'icon' => 'palette', 'folder' => 'branding',
            'intro' => 'Logo, favicon, business name and search engine details.',
            'fields' => [
                'site_name'        => ['type' => 'text', 'label' => 'Business name', 'required' => true, 'half' => true],
                'site_tagline'     => ['type' => 'text', 'label' => 'Tagline (under the logo)', 'half' => true],
                'logo'             => ['type' => 'image', 'label' => 'Logo', 'help' => 'Transparent PNG works best.', 'half' => true],
                'favicon'          => ['type' => 'image', 'label' => 'Favicon (browser tab icon)', 'help' => 'Square PNG or ICO, 64 × 64 px.', 'half' => true],
                'apple_touch_icon' => ['type' => 'image', 'label' => 'Phone home-screen icon', 'help' => 'Square PNG, 180 × 180 px.', 'half' => true],
                'meta_title'       => ['type' => 'text', 'label' => 'SEO title (browser tab & Google)', 'counter' => 60,
                                       'help' => 'Best under 60 characters.'],
                'meta_description' => ['type' => 'textarea', 'label' => 'SEO description', 'rows' => 3, 'counter' => 160,
                                       'help' => 'Shown under your title in Google results. Best 120–160 characters.'],
                'meta_keywords'    => ['type' => 'textarea', 'label' => 'SEO keywords', 'rows' => 3, 'counter' => 255,
                                       'placeholder' => 'catering services, south indian catering, wedding catering',
                                       'help' => 'Separate keywords with commas.'],
                'og_image'         => ['type' => 'image', 'label' => 'Social share image', 'half' => true,
                                       'help' => 'Shown when the site link is shared on WhatsApp / Facebook. 1200 × 630 px.'],
                'theme_page_bg'    => ['type' => 'image', 'label' => 'Page background pattern', 'half' => true],
                'theme_divider'    => ['type' => 'image', 'label' => 'Gold section divider', 'half' => true],
                'theme_flourish'   => ['type' => 'image', 'label' => 'Leaf flourish (events section)', 'half' => true],
                'theme_arch'       => ['type' => 'image', 'label' => 'Events arch image (green arch with gold edge)', 'half' => true,
                                       'help' => 'Transparent PNG, about 1700 × 760 px. Leave empty to use the built-in arch.'],
            ],
        ],
        'about' => [
            'title' => 'About Section', 'icon' => 'book-open', 'folder' => 'about',
            'intro' => 'The "Tradition Served with Excellence" section.',
            'fields' => [
                'about_enabled' => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'about_eyebrow' => ['type' => 'text', 'label' => 'Small label', 'half' => true],
                'about_title'   => ['type' => 'text', 'label' => 'Heading', 'half' => true],
                'about_text'    => ['type' => 'textarea', 'label' => 'First paragraph', 'rows' => 4],
                'about_text_2'  => ['type' => 'textarea', 'label' => 'Second paragraph', 'rows' => 3],
                'about_image_1' => ['type' => 'image', 'label' => 'Main photo', 'half' => true],
                'about_image_2' => ['type' => 'image', 'label' => 'Small overlapping photo', 'half' => true],
            ],
        ],
        'contact' => [
            'title' => 'Contact Section', 'icon' => 'phone', 'folder' => 'misc',
            'intro' => 'Contact details and the enquiry form text. Phone, email and Instagram also appear in the footer.',
            'fields' => [
                'contact_enabled'         => ['type' => 'toggle', 'label' => 'Show this section on the website'],
                'contact_eyebrow'         => ['type' => 'text', 'label' => 'Small label', 'half' => true],
                'contact_title'           => ['type' => 'text', 'label' => 'Heading', 'half' => true],
                'contact_text'            => ['type' => 'textarea', 'label' => 'Intro text', 'rows' => 2],
                'contact_phone'           => ['type' => 'text', 'label' => 'Phone', 'half' => true],
                'contact_whatsapp'        => ['type' => 'text', 'label' => 'WhatsApp number (with country code)', 'half' => true, 'placeholder' => '918402399450'],
                'contact_email'           => ['type' => 'email', 'label' => 'Email', 'half' => true],
                'contact_instagram'       => ['type' => 'text', 'label' => 'Instagram handle', 'half' => true],
                'contact_instagram_url'   => ['type' => 'url', 'label' => 'Instagram link'],
                'contact_address'         => ['type' => 'textarea', 'label' => 'Address (optional)', 'rows' => 2],
                'enquiry_min_guests'      => ['type' => 'number', 'label' => 'Minimum guests accepted', 'half' => true, 'min' => 1, 'max' => 100000, 'required' => true],
                'enquiry_max_guests'      => ['type' => 'number', 'label' => 'Maximum guests accepted', 'half' => true, 'min' => 1, 'max' => 100000, 'required' => true,
                                              'help' => 'The enquiry form rejects guest numbers outside this range.'],
                'contact_button_text'     => ['type' => 'text', 'label' => 'Form button text'],
                'contact_success_message' => ['type' => 'textarea', 'label' => 'Message shown after the form is sent', 'rows' => 2],
            ],
        ],
        'floating' => [
            'title' => 'Floating Buttons', 'icon' => 'message-circle', 'folder' => 'misc',
            'intro' => 'Round quick-contact buttons fixed at the bottom-right of the website. Switch off any you do not need.',
            'fields' => [
                'float_whatsapp_enabled' => ['type' => 'toggle', 'label' => 'Show WhatsApp button (uses the WhatsApp number from Contact Section)'],
                'float_whatsapp_message' => ['type' => 'textarea', 'label' => 'WhatsApp pre-filled message', 'rows' => 2, 'placeholder' => 'Hello BB Caterers, I would like to enquire about catering.'],
                'float_call_enabled'     => ['type' => 'toggle', 'label' => 'Show Call button (uses the phone number from Contact Section)'],
                'float_instagram_enabled' => ['type' => 'toggle', 'label' => 'Show Instagram button (uses the Instagram link from Contact Section)'],
                'float_top_enabled'      => ['type' => 'toggle', 'label' => 'Show Back-to-top button'],
            ],
        ],
        'footer' => [
            'title' => 'Call-to-Action & Footer', 'icon' => 'panel-bottom', 'folder' => 'misc',
            'intro' => 'The green "Good Food. Beautiful Moments." band and the footer.',
            'fields' => [
                'cta_enabled'      => ['type' => 'toggle', 'label' => 'Show the call-to-action band'],
                'cta_line_1'       => ['type' => 'text', 'label' => 'Band line 1', 'half' => true],
                'cta_line_2'       => ['type' => 'text', 'label' => 'Band line 2 (gold italic)', 'half' => true],
                'cta_button_text'  => ['type' => 'text', 'label' => 'Button text', 'half' => true],
                'cta_button_link'  => ['type' => 'text', 'label' => 'Button link', 'half' => true],
                'footer_about'     => ['type' => 'textarea', 'label' => 'Footer description', 'rows' => 3],
                'footer_copyright' => ['type' => 'text', 'label' => 'Copyright line', 'help' => '{year} is replaced with the current year.'],
                'footer_credit'           => ['type' => 'text', 'label' => 'Credit text', 'half' => true, 'placeholder' => 'Designed & Developed by'],
                'footer_credit_link_text' => ['type' => 'text', 'label' => 'Credit link text', 'half' => true, 'placeholder' => 'Vetri IT Systems'],
                'footer_credit_url'       => ['type' => 'url', 'label' => 'Credit link address', 'placeholder' => 'https://vetriitsystems.com/'],
                'social_facebook'  => ['type' => 'url', 'label' => 'Facebook link (optional)', 'half' => true],
                'social_youtube'   => ['type' => 'url', 'label' => 'YouTube link (optional)', 'half' => true],
            ],
        ],
    ];
}

/**
 * Read submitted values for a field set. Uploads are stored; old images are cleaned up.
 * Returns [values, errors].
 */
function collect_fields(array $fields, string $folder, array $current = []): array
{
    $values = [];
    $errors = [];
    foreach ($fields as $name => $f) {
        $type = $f['type'];
        $label = $f['label'];
        if ($type === 'image') {
            try {
                $new = handle_upload($name, $folder);
            } catch (RuntimeException $ex) {
                $errors[$name] = $ex->getMessage();
                continue;
            }
            $old = $current[$name] ?? null;
            if ($new) {
                $values[$name] = $new;
            } elseif (!empty($_POST[$name . '__remove'])) {
                $values[$name] = null;
            } else {
                $values[$name] = $old;
            }
            if (!empty($f['required']) && empty($values[$name])) {
                $errors[$name] = "$label is required.";
            }
            continue;
        }
        if ($type === 'toggle') {
            $values[$name] = !empty($_POST[$name]) ? '1' : '0';
            continue;
        }
        $v = trim((string) ($_POST[$name] ?? ''));
        if (!empty($f['required']) && $v === '') {
            $errors[$name] = "$label is required.";
        }
        if ($v !== '') {
            if ($type === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $errors[$name] = "$label must be a valid email address.";
            }
            if ($type === 'url' && !filter_var($v, FILTER_VALIDATE_URL)) {
                $errors[$name] = "$label must be a full link starting with https://";
            }
            if ($type === 'number' && !is_numeric($v)) {
                $errors[$name] = "$label must be a number.";
            }
            if ($type === 'select' && !array_key_exists($v, $f['options'])) {
                $errors[$name] = "Choose a valid $label.";
            }
            if ($type === 'icon' && !in_array($v, ICON_CHOICES, true)) {
                $errors[$name] = 'Choose an icon from the list.';
            }
            if ($type === 'rating') {
                $v = (string) max(1, min(5, (int) $v));
            }
        }
        $values[$name] = $v === '' ? null : $v;
    }
    return [$values, $errors];
}

/** After a successful save, delete images that were replaced or removed. */
function cleanup_replaced_images(array $fields, array $old, array $new): void
{
    foreach ($fields as $name => $f) {
        if ($f['type'] === 'image' && !empty($old[$name]) && ($new[$name] ?? null) !== $old[$name]) {
            delete_media($old[$name]);
        }
    }
}
